<?php
session_start();
require_once 'db.php';
require_once 'csrf.php';
require_once 'includes/icons.php';

// Redirect if already logged in
if (isset($_SESSION['emp_id'])) {
  header('Location: employee_portal.php');
  exit;
}

// Get settings
$mysqli = db_connect();
$settings_query = $mysqli->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $settings_query->fetch_assoc()) {
  $settings[$row['setting_key']] = $row['setting_value'];
}

$company_name = $settings['company_name'] ?? 'BPJS Ketenagakerjaan';
$company_tagline = $settings['company_tagline'] ?? 'Melindungi Pekerja Indonesia';

$error = '';
$csrf = csrf_token();

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf_token'] ?? '';
  if (!csrf_check($token)) {
    $error = 'Token CSRF tidak valid. Silakan reload halaman.';
  } else {
    $employee_code = trim($_POST['employee_code'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($employee_code) || empty($password)) {
      $error = 'Kode karyawan dan password harus diisi!';
    } else {
      $stmt = $mysqli->prepare("SELECT id, name, password, status FROM employees WHERE employee_code = ?");
      $stmt->bind_param('s', $employee_code);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();

        if ($employee['status'] !== 'active') {
          $error = 'Akun Anda tidak aktif. Hubungi administrator.';
        } elseif (empty($employee['password'])) {
          $error = 'Password belum diatur. Hubungi administrator.';
        } elseif (password_verify($password, $employee['password'])) {
          // Login successful
          $_SESSION['emp_id'] = $employee['id'];
          $_SESSION['emp_code'] = $employee_code;
          $_SESSION['emp_name'] = $employee['name'];

          // Log activity
          $ip = $_SERVER['REMOTE_ADDR'] ?? '';
          $log_stmt = $mysqli->prepare("INSERT INTO activity_logs (user_type, user_id, action, description, ip_addr) VALUES ('employee', ?, 'login', 'Employee login', ?)");
          $log_stmt->bind_param('is', $employee['id'], $ip);
          $log_stmt->execute();

          header('Location: employee_portal.php');
          exit;
        } else {
          $error = 'Password salah!';
        }
      } else {
        $error = 'Kode karyawan tidak ditemukan!';
      }
    }
  }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Portal Karyawan <?php echo htmlspecialchars($company_name); ?>">
  <title>Portal Karyawan - <?php echo htmlspecialchars($company_name); ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/backgrounds.css">
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background: linear-gradient(135deg, var(--bpjs-blue) 0%, var(--bpjs-blue-dark) 100%);
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url("data:image/svg+xml,%3Csvg width='100' height='100' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='dots' width='20' height='20' patternUnits='userSpaceOnUse'%3E%3Ccircle cx='10' cy='10' r='1.5' fill='white' fill-opacity='0.1'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100' height='100' fill='url(%23dots)'/%3E%3C/svg%3E");
      pointer-events: none;
    }

    .login-container {
      width: 100%;
      max-width: 480px;
      padding: var(--space-4);
    }

    .login-card {
      background: white;
      border-radius: var(--radius-lg);
      padding: var(--space-8);
      box-shadow: var(--shadow-xl);
      border: 1px solid var(--gray-200);
    }

    .login-header {
      text-align: center;
      margin-bottom: var(--space-8);
    }

    .logo-wrapper {
      display: flex;
      justify-content: center;
      margin-bottom: var(--space-4);
    }

    .login-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto var(--space-4);
      background: var(--primary-50);
      border-radius: var(--radius-lg);
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid var(--bpjs-blue);
    }

    .login-icon svg {
      width: 40px;
      height: 40px;
      color: var(--bpjs-blue);
    }

    .login-title {
      font-size: var(--text-2xl);
      font-weight: 700;
      color: var(--gray-900);
      margin-bottom: var(--space-2);
    }

    .login-subtitle {
      color: var(--gray-600);
      font-size: var(--text-sm);
    }

    .company-info {
      text-align: center;
      margin-bottom: var(--space-6);
      padding-bottom: var(--space-4);
      border-bottom: 2px solid var(--gray-100);
    }

    .company-name {
      font-size: var(--text-lg);
      font-weight: 700;
      color: var(--bpjs-blue);
      margin-bottom: var(--space-1);
    }

    .company-tagline {
      font-size: var(--text-xs);
      color: var(--gray-500);
    }

    .back-link {
      text-align: center;
      margin-top: var(--space-6);
    }

    .back-link a {
      color: white;
      text-decoration: none;
      font-weight: 600;
      transition: all var(--transition-base);
      display: inline-flex;
      align-items: center;
      gap: var(--space-2);
      padding: var(--space-2) var(--space-4);
      border-radius: var(--radius-md);
    }

    .back-link a:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .back-link svg {
      width: 16px;
      height: 16px;
    }

    @media (max-width: 640px) {
      .login-card {
        padding: var(--space-6);
      }
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="login-card">
      <div class="company-info">
        <div class="logo-wrapper">
          <img src="assets/images/logo-bpjs.png" alt="<?php echo htmlspecialchars($company_name); ?>"
            style="height: 50px;">
        </div>
        <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
        <div class="company-tagline"><?php echo htmlspecialchars($company_tagline); ?></div>
      </div>

      <div class="login-header">
        <div class="login-icon">
          <?php echo SVGIcons::user(); ?>
        </div>
        <h1 class="login-title">Portal Karyawan</h1>
        <p class="login-subtitle">Masuk untuk mengakses portal Anda</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <div style="display: flex; align-items: start; gap: var(--space-2);">
            <?php echo SVGIcons::alertTriangle(); ?>
            <span><?php echo htmlspecialchars($error); ?></span>
          </div>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">

        <div class="form-group">
          <label class="form-label">Kode Karyawan</label>
          <input type="text" name="employee_code" class="form-input" placeholder="Contoh: EMP001" required autofocus
            value="<?php echo htmlspecialchars($_POST['employee_code'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-input" placeholder="Masukkan password" required>
          <small class="text-gray-500">Jika lupa password, hubungi administrator</small>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
          <?php echo SVGIcons::login(); ?>
          <span>Masuk ke Portal</span>
        </button>
      </form>
    </div>

    <div class="back-link">
      <a href="index.php">
        <?php echo SVGIcons::arrowLeft(); ?>
        <span>Kembali ke Halaman Utama</span>
      </a>
    </div>
  </div>
</body>

</html>