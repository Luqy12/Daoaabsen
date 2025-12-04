<?php
session_start();
require_once 'db.php';
require_once 'includes/icons.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$mysqli = db_connect();
$admin_name = $_SESSION['admin_name'];

// Get settings
$settings_query = $mysqli->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $settings_query->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$company_name = $settings['company_name'] ?? 'BPJS Ketenagakerjaan';

// Get statistics for today
$today = date('Y-m-d');
$stats = [];

// Total employees
$result = $mysqli->query("SELECT COUNT(*) as total FROM employees WHERE status = 'active'");
$stats['total_employees'] = $result->fetch_assoc()['total'];

// Today's check-ins
$result = $mysqli->query("SELECT COUNT(DISTINCT employee_id) as total FROM attendances WHERE DATE(created_at) = '$today' AND type = 'checkin'");
$stats['today_checkins'] = $result->fetch_assoc()['total'];

// Today's late arrivals
$result = $mysqli->query("SELECT COUNT(*) as total FROM attendances WHERE DATE(created_at) = '$today' AND type = 'checkin' AND status IN ('late', 'very_late')");
$stats['today_late'] = $result->fetch_assoc()['total'];

// Today's permits (izin/sakit/cuti)
$result = $mysqli->query("SELECT COUNT(*) as total FROM attendances WHERE DATE(created_at) = '$today' AND type IN ('izin', 'sakit', 'cuti')");
$stats['today_permits'] = $result->fetch_assoc()['total'];

// Recent attendances
$recent_query = "SELECT a.*, e.employee_code, e.name, e.photo, d.name as dept_name 
                 FROM attendances a 
                 JOIN employees e ON a.employee_id = e.id 
                 LEFT JOIN departments d ON e.department_id = d.id
                 ORDER BY a.created_at DESC LIMIT 10";
$recent_attendances = $mysqli->query($recent_query)->fetch_all(MYSQLI_ASSOC);

// Chart data: Last 7 days attendance
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $result = $mysqli->query("SELECT COUNT(DISTINCT employee_id) as total FROM attendances WHERE DATE(created_at) = '$date' AND type = 'checkin'");
    $chart_data[] = [
        'date' => date('d M', strtotime($date)),
        'count' => $result->fetch_assoc()['total']
    ];
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard Admin - <?php echo htmlspecialchars($company_name); ?>">
    <title>Dashboard Admin - <?php echo htmlspecialchars($company_name); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/backgrounds.css">
    <style>
        body {
            background: var(--gray-50);
        }

        .navbar {
            background: white;
            padding: var(--space-4) 0;
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-6);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 2px solid var(--gray-200);
        }

        .navbar-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 var(--space-6);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .navbar-brand-text {
            font-size: var(--text-xl);
            font-weight: 700;
            color: var(--gray-900);
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-4);
            background: var(--gray-50);
            border-radius: var(--radius-md);
        }

        .user-info svg {
            width: 20px;
            height: 20px;
            color: var(--bpjs-blue);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-6);
            margin-bottom: var(--space-8);
        }

        .stat-card {
            background: white;
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            transition: all var(--transition-base);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .stat-card-1 {
            border-left: 4px solid var(--bpjs-blue);
        }

        .stat-card-2 {
            border-left: 4px solid var(--bpjs-green);
        }

        .stat-card-3 {
            border-left: 4px solid var(--warning);
        }

        .stat-card-4 {
            border-left: 4px solid var(--info);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: var(--space-3);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card-1 .stat-icon {
            background: rgba(0, 102, 204, 0.1);
        }

        .stat-card-2 .stat-icon {
            background: rgba(0, 166, 81, 0.1);
        }

        .stat-card-3 .stat-icon {
            background: rgba(255, 193, 7, 0.1);
        }

        .stat-card-4 .stat-icon {
            background: rgba(23, 162, 184, 0.1);
        }

        .stat-icon svg {
            width: 24px;
            height: 24px;
        }

        .stat-card-1 .stat-icon svg {
            color: var(--bpjs-blue);
        }

        .stat-card-2 .stat-icon svg {
            color: var(--bpjs-green);
        }

        .stat-card-3 .stat-icon svg {
            color: var(--warning);
        }

        .stat-card-4 .stat-icon svg {
            color: var(--info);
        }

        .stat-value {
            font-size: var(--text-4xl);
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: var(--space-1);
        }

        .stat-label {
            font-size: var(--text-sm);
            color: var(--gray-600);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .chart-container {
            background: white;
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--space-6);
        }

        .chart-header {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            margin-bottom: var(--space-6);
        }

        .chart-header svg {
            width: 24px;
            height: 24px;
            color: var(--bpjs-blue);
        }

        .chart-canvas {
            max-height: 300px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--space-4);
            margin-bottom: var(--space-6);
        }

        .menu-card {
            background: white;
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            text-align: center;
            transition: all var(--transition-base);
            text-decoration: none;
            color: var(--gray-800);
            border: 2px solid transparent;
        }

        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: var(--bpjs-blue);
        }

        .menu-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto var(--space-4);
            background: var(--primary-50);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-icon svg {
            width: 32px;
            height: 32px;
            color: var(--bpjs-blue);
        }

        .menu-title {
            font-weight: 700;
            font-size: var(--text-lg);
            color: var(--gray-900);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            margin-bottom: var(--space-4);
        }

        .section-title svg {
            width: 24px;
            height: 24px;
            color: var(--bpjs-blue);
        }

        .activity-item {
            display: flex;
            gap: var(--space-4);
            padding: var(--space-4);
            border-bottom: 1px solid var(--gray-100);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-avatar {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-full);
            background: var(--bpjs-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: var(--text-lg);
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-name {
            font-weight: 600;
            color: var(--gray-900);
        }

        .activity-time {
            font-size: var(--text-sm);
            color: var(--gray-500);
            margin-top: var(--space-1);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">
                <img src="assets/images/logo-bpjs.png" alt="<?php echo htmlspecialchars($company_name); ?>"
                    style="height: 40px;">
                <span class="navbar-brand-text">Dashboard Admin</span>
            </div>
            <div class="navbar-user">
                <div class="user-info">
                    <?php echo SVGIcons::user(); ?>
                    <strong><?php echo htmlspecialchars($admin_name); ?></strong>
                </div>
                <a href="logout.php" class="btn btn-sm btn-outline">
                    <?php echo SVGIcons::logout(); ?>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-lg">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-1">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $stats['total_employees']; ?></div>
                        <div class="stat-label">Total Karyawan Aktif</div>
                    </div>
                    <div class="stat-icon">
                        <?php echo SVGIcons::users(); ?>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-card-2">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $stats['today_checkins']; ?></div>
                        <div class="stat-label">Hadir Hari Ini</div>
                    </div>
                    <div class="stat-icon">
                        <?php echo SVGIcons::checkCircle(); ?>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-card-3">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $stats['today_late']; ?></div>
                        <div class="stat-label">Terlambat Hari Ini</div>
                    </div>
                    <div class="stat-icon">
                        <?php echo SVGIcons::clock(); ?>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-card-4">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $stats['today_permits']; ?></div>
                        <div class="stat-label">Izin Hari Ini</div>
                    </div>
                    <div class="stat-icon">
                        <?php echo SVGIcons::file(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Menu -->
        <h2 class="section-title">
            <?php echo SVGIcons::dashboard(); ?>
            <span>Menu Utama</span>
        </h2>
        <div class="menu-grid">
            <a href="admin.php" class="menu-card">
                <div class="menu-icon">
                    <?php echo SVGIcons::barChart(); ?>
                </div>
                <div class="menu-title">Data Absensi</div>
            </a>
            <a href="employees.php" class="menu-card">
                <div class="menu-icon">
                    <?php echo SVGIcons::users(); ?>
                </div>
                <div class="menu-title">Kelola Karyawan</div>
            </a>
            <a href="reports.php" class="menu-card">
                <div class="menu-icon">
                    <?php echo SVGIcons::download(); ?>
                </div>
                <div class="menu-title">Laporan</div>
            </a>
            <a href="settings.php" class="menu-card">
                <div class="menu-icon">
                    <?php echo SVGIcons::settings(); ?>
                </div>
                <div class="menu-title">Pengaturan</div>
            </a>
        </div>

        <!-- Chart -->
        <div class="chart-container">
            <h3 class="chart-header">
                <?php echo SVGIcons::barChart(); ?>
                <span>Grafik Kehadiran 7 Hari Terakhir</span>
            </h3>
            <canvas id="attendanceChart" class="chart-canvas"></canvas>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h3 class="section-title" style="margin-bottom: 0;">
                    <?php echo SVGIcons::clock(); ?>
                    <span>Aktivitas Terbaru</span>
                </h3>
            </div>

            <?php if (empty($recent_attendances)): ?>
                <p class="text-gray-500 text-center" style="padding: var(--space-6);">Belum ada aktivitas.</p>
            <?php else: ?>
                <?php foreach ($recent_attendances as $att): ?>
                    <div class="activity-item">
                        <div class="activity-avatar">
                            <?php echo strtoupper(substr($att['name'], 0, 1)); ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-name"><?php echo htmlspecialchars($att['name']); ?></div>
                            <div class="activity-time">
                                <span class="badge badge-<?php
                                echo $att['type'] === 'checkin' ? 'primary' :
                                    ($att['type'] === 'checkout' ? 'success' : 'warning');
                                ?>">
                                    <?php echo strtoupper($att['type']); ?>
                                </span>
                                <?php if ($att['status'] === 'late' || $att['status'] === 'very_late'): ?>
                                    <span class="badge badge-danger">TERLAMBAT</span>
                                <?php endif; ?>
                                - <?php echo date('d M Y H:i', strtotime($att['created_at'])); ?>
                                <?php if ($att['dept_name']): ?>
                                    <span class="text-gray-400">• <?php echo htmlspecialchars($att['dept_name']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const chartData = <?php echo json_encode($chart_data); ?>;

        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [{
                    label: 'Jumlah Hadir',
                    data: chartData.map(d => d.count),
                    borderColor: '#0066CC',
                    backgroundColor: 'rgba(0, 102, 204, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#0066CC',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
        }
 });
    </script>
</body>

</html>