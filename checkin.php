<?php
// checkin.php - Handle attendance submission
require_once 'db.php';
require_once 'csrf.php';

header('Content-Type: application/json');

// CSRF validation
$token = $_POST['csrf_token'] ?? '';
if (!csrf_check($token)) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid. Silakan reload halaman.']);
    exit;
}

// Get form data
$code = isset($_POST['employee_code']) ? trim($_POST['employee_code']) : '';
$lat = isset($_POST['lat']) ? $_POST['lat'] : null;
$lon = isset($_POST['lon']) ? $_POST['lon'] : null;
$type = isset($_POST['type']) ? $_POST['type'] : 'checkin';
$note = isset($_POST['note']) ? trim($_POST['note']) : null;

// Validate employee code
if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Kode karyawan diperlukan.']);
    exit;
}

// Validate type
if (!in_array($type, ['checkin', 'checkout', 'izin', 'sakit', 'cuti'])) {
    $type = 'checkin';
}

$mysqli = db_connect();

// Find employee
$stmt = $mysqli->prepare("SELECT id, name, department_id, status FROM employees WHERE employee_code = ?");
$stmt->bind_param('s', $code);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Karyawan tidak ditemukan.']);
    $mysqli->close();
    exit;
}

$employee = $res->fetch_assoc();
$employee_id = $employee['id'];

// Check if employee is active
if ($employee['status'] !== 'active') {
    echo json_encode(['success' => false, 'message' => 'Akun karyawan tidak aktif. Hubungi administrator.']);
    $mysqli->close();
    exit;
}

// Get work hours from department
$work_start = '08:00:00';
$late_tolerance = 15;
if ($employee['department_id']) {
    $dept_stmt = $mysqli->prepare("SELECT work_start, late_tolerance FROM departments WHERE id = ?");
    $dept_stmt->bind_param('i', $employee['department_id']);
    $dept_stmt->execute();
    $dept_res = $dept_stmt->get_result();
    if ($dept_res->num_rows > 0) {
        $dept = $dept_res->fetch_assoc();
        $work_start = $dept['work_start'];
        $late_tolerance = $dept['late_tolerance'];
    }
}

// Determine status for checkin
$status = 'on_time';
if ($type === 'checkin') {
    $current_time = new DateTime();
    $work_time = new DateTime(date('Y-m-d') . ' ' . $work_start);
    $work_time->modify("+{$late_tolerance} minutes");

    if ($current_time > $work_time) {
        $diff = $current_time->diff($work_time);
        $minutes_late = ($diff->h * 60) + $diff->i;
        if ($minutes_late > 30) {
            $status = 'very_late';
        } else {
            $status = 'late';
        }
    }
} elseif (in_array($type, ['izin', 'sakit', 'cuti'])) {
    $status = 'permit';
}

// Handle photo upload
$photo_path = null;
if (!empty($_FILES['photo']['name'])) {
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (!$ext)
        $ext = 'jpg';

    // Validate extension
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed_ext)) {
        echo json_encode(['success' => false, 'message' => 'Format foto tidak valid. Gunakan JPG, PNG, atau GIF.']);
        $mysqli->close();
        exit;
    }

    $base = 'att_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadDir . '/' . $base;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
        $photo_path = 'uploads/' . $base;
    }
}

// Calculate distance from office (if GPS provided)
$distance_from_office = null;
if ($lat && $lon) {
    // Get office coordinates from settings
    $office_lat = -6.2088;
    $office_lon = 106.8456;

    $settings_query = $mysqli->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('office_lat', 'office_lon')");
    while ($setting = $settings_query->fetch_assoc()) {
        if ($setting['setting_key'] === 'office_lat') {
            $office_lat = floatval($setting['setting_value']);
        } elseif ($setting['setting_key'] === 'office_lon') {
            $office_lon = floatval($setting['setting_value']);
        }
    }

    // Calculate distance using Haversine formula
    $earth_radius = 6371000; // meters
    $lat1 = deg2rad($office_lat);
    $lat2 = deg2rad($lat);
    $lon1 = deg2rad($office_lon);
    $lon2 = deg2rad($lon);

    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;

    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance_from_office = $earth_radius * $c;
}

// Insert attendance record
$stmt = $mysqli->prepare("INSERT INTO attendances (employee_id, `type`, status, lat, lon, note, photo_path, distance_from_office, ip_addr, user_agent) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$stmt->bind_param('issddssdss', $employee_id, $type, $status, $lat, $lon, $note, $photo_path, $distance_from_office, $ip, $user_agent);

if ($stmt->execute()) {
    // Log activity
    $log_stmt = $mysqli->prepare("INSERT INTO activity_logs (user_type, user_id, action, description, ip_addr) VALUES ('employee', ?, 'attendance', ?, ?)");
    $desc = "Absensi: {$type} - {$status}";
    $log_stmt->bind_param('iss', $employee_id, $desc, $ip);
    $log_stmt->execute();

    $response = [
        'success' => true,
        'message' => 'Absensi berhasil dicatat.',
        'employee' => $employee['name'],
        'type' => $type,
        'status' => $status,
        'time' => date('H:i:s'),
        'distance' => $distance_from_office ? round($distance_from_office) : null
    ];

    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $mysqli->error]);
}

$mysqli->close();
?>