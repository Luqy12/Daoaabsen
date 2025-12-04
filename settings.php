<?php
session_start();
require_once 'db.php';
require_once 'includes/icons.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$mysqli = db_connect();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'company_name' => $_POST['company_name'],
        'company_address' => $_POST['company_address'],
        'company_tagline' => $_POST['company_tagline'],
        'company_phone' => $_POST['company_phone'],
        'company_website' => $_POST['company_website']
    ];

    foreach ($settings as $key => $value) {
        $stmt = $mysqli->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->bind_param('ss', $value, $key);
        $stmt->execute();
    }
    $message = 'Pengaturan berhasil disimpan!';
}

// Get current settings
$settings_query = $mysqli->query("SELECT setting_key, setting_value FROM settings");
$current_settings = [];
while ($row = $settings_query->fetch_assoc()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - <?php echo htmlspecialchars($current_settings['company_name'] ?? 'Sistem Absensi'); ?></title>
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
            max-width: 800px;
            margin: var(--space-8) auto;
            padding: 0 var(--space-6);
        }

        .card {
            background: white;
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
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

        .alert-success {
            background: var(--success-light);
            color: var(--success-dark);
            padding: var(--space-4);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-4);
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">
                <img src="assets/images/logo-bpjs.png" alt="Logo" style="height: 32px;">
                <span>Pengaturan Sistem</span>
            </div>
            <a href="dashboard.php" class="btn btn-outline">Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Perusahaan</label>
                    <input type="text" name="company_name" class="form-control"
                        value="<?php echo htmlspecialchars($current_settings['company_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tagline</label>
                    <input type="text" name="company_tagline" class="form-control"
                        value="<?php echo htmlspecialchars($current_settings['company_tagline'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <textarea name="company_address" class="form-control"
                        rows="3"><?php echo htmlspecialchars($current_settings['company_address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="company_phone" class="form-control"
                        value="<?php echo htmlspecialchars($current_settings['company_phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="text" name="company_website" class="form-control"
                        value="<?php echo htmlspecialchars($current_settings['company_website'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </form>
        </div>
    </div>
</body>

</html>