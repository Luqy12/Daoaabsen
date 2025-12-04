<?php
session_start();
require_once 'db.php';
require_once 'includes/icons.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$mysqli = db_connect();
$id = (int) $_GET['id'];

// Get attendance detail
$query = "SELECT a.*, e.employee_code, e.name, e.photo, d.name as dept_name 
          FROM attendances a 
          JOIN employees e ON a.employee_id = e.id 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE a.id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$attendance = $stmt->get_result()->fetch_assoc();

if (!$attendance) {
    die("Data tidak ditemukan.");
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Absensi - <?php echo htmlspecialchars($attendance['name']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background: var(--gray-50);
        }

        .navbar {
            background: white;
            padding: var(--space-4) 0;
            box-shadow: var(--shadow-sm);
            border-bottom: 2px solid var(--gray-200);
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--space-6);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 1000px;
            margin: var(--space-8) auto;
            padding: 0 var(--space-6);
        }

        .card {
            background: white;
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-8);
        }

        .photo-container {
            text-align: center;
            margin-bottom: var(--space-6);
        }

        .photo-container img {
            max-width: 100%;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: var(--space-3) 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .info-label {
            font-weight: 600;
            color: var(--gray-600);
        }

        .info-value {
            font-weight: 500;
            color: var(--gray-900);
        }

        #map {
            height: 300px;
            border-radius: var(--radius-lg);
            margin-top: var(--space-4);
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand" style="font-weight: 700; font-size: var(--text-xl);">
                <?php echo SVGIcons::file(); ?> Detail Absensi
            </div>
            <a href="admin.php" class="btn btn-outline">Kembali</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="detail-grid">
                <div>
                    <h3 class="mb-4">Informasi Karyawan</h3>
                    <div class="info-row">
                        <span class="info-label">Nama</span>
                        <span class="info-value"><?php echo htmlspecialchars($attendance['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Kode Karyawan</span>
                        <span class="info-value"><?php echo htmlspecialchars($attendance['employee_code']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Departemen</span>
                        <span class="info-value"><?php echo htmlspecialchars($attendance['dept_name'] ?? '-'); ?></span>
                    </div>

                    <h3 class="mb-4 mt-6">Detail Kehadiran</h3>
                    <div class="info-row">
                        <span class="info-label">Waktu</span>
                        <span
                            class="info-value"><?php echo date('d M Y H:i', strtotime($attendance['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Jenis</span>
                        <span class="info-value">
                            <span
                                class="badge badge-<?php echo $attendance['type'] === 'checkin' ? 'info' : ($attendance['type'] === 'checkout' ? 'success' : 'warning'); ?>">
                                <?php echo strtoupper($attendance['type']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span
                                class="badge badge-<?php echo $attendance['status'] === 'on_time' ? 'success' : 'danger'; ?>">
                                <?php echo strtoupper(str_replace('_', ' ', $attendance['status'])); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Keterangan</span>
                        <span class="info-value"><?php echo htmlspecialchars($attendance['note'] ?? '-'); ?></span>
                    </div>
                </div>

                <div>
                    <h3 class="mb-4">Bukti Foto & Lokasi</h3>
                    <?php if ($attendance['photo_path']): ?>
                        <div class="photo-container">
                            <img src="<?php echo htmlspecialchars($attendance['photo_path']); ?>" alt="Bukti Foto">
                        </div>
                    <?php else: ?>
                        <div class="text-center text-gray-500 mb-6">Tidak ada foto</div>
                    <?php endif; ?>

                    <?php if ($attendance['lat'] && $attendance['lon']): ?>
                        <div id="map"></div>
                    <?php else: ?>
                        <div class="text-center text-gray-500">Tidak ada data lokasi</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        <?php if ($attendance['lat'] && $attendance['lon']): ?>
            const map = L.map('map').setView([<?php echo $attendance['lat']; ?>, <?php echo $attendance['lon']; ?>], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
            L.marker([<?php echo $attendance['lat']; ?>, <?php echo $attendance['lon']; ?>]).addTo(map)
                .bindPopup("Lokasi Absensi").openPopup();
        <?php endif; ?>
    </script>
</body>

</html>