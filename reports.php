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

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - <?php echo htmlspecialchars($company_name); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
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

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            font-weight: 700;
            font-size: var(--text-xl);
            color: var(--gray-900);
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
            margin-bottom: var(--space-6);
        }

        .form-group {
            margin-bottom: var(--space-4);
        }

        .form-label {
            display: block;
            margin-bottom: var(--space-2);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: var(--space-3);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
        }

        .btn-primary {
            background: var(--bpjs-blue);
            color: white;
            border: none;
            padding: var(--space-3) var(--space-6);
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: var(--bpjs-blue-dark);
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">
                <img src="assets/images/logo-bpjs.png" alt="Logo" style="height: 32px;">
                <span>Laporan Absensi</span>
            </div>
            <a href="dashboard.php" class="btn btn-outline">Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2 class="mb-4">Export Laporan Bulanan</h2>
            <form action="export.php" method="GET">
                <div class="form-group">
                    <label class="form-label">Bulan</label>
                    <input type="month" name="month" class="form-control" value="<?php echo date('Y-m'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Departemen (Opsional)</label>
                    <select name="department_id" class="form-control">
                        <option value="">Semua Departemen</option>
                        <!-- Populate departments here if needed -->
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <?php echo SVGIcons::download(); ?> Download Excel
                </button>
            </form>
        </div>
    </div>
</body>

</html>