<?php
session_start();
require_once 'db.php';
require_once 'csrf.php';
require_once 'includes/icons.php';
$csrf = csrf_token();

// Get settings
$mysqli = db_connect();
$settings_query = $mysqli->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $settings_query->fetch_assoc()) {
  $settings[$row['setting_key']] = $row['setting_value'];
}
$mysqli->close();

$company_name = $settings['company_name'] ?? 'BPJS Ketenagakerjaan';
$company_tagline = $settings['company_tagline'] ?? 'Melindungi Pekerja Indonesia';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf); ?>">
  <meta name="description" content="Sistem Absensi Karyawan <?php echo htmlspecialchars($company_name); ?>">
  <title><?php echo htmlspecialchars($company_name); ?> - Sistem Absensi Karyawan</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/backgrounds.css">
  <style>
    .header {
      background: white;
      border-bottom: 2px solid var(--gray-200);
      padding: var(--space-4) 0;
      box-shadow: var(--shadow-sm);
    }

    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 var(--space-4);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: var(--space-4);
    }

    .logo-container {
      display: flex;
      flex-direction: column;
      gap: var(--space-1);
    }

    .company-tagline {
      font-size: var(--text-xs);
      color: var(--gray-600);
      font-weight: 500;
    }

    .hero {
      background: linear-gradient(135deg, var(--bpjs-blue) 0%, var(--bpjs-blue-dark) 100%);
      text-align: center;
      padding: var(--space-12) var(--space-4);
      color: white;
    }

    .hero-title {
      font-size: var(--text-3xl);
      font-weight: 700;
      margin-bottom: var(--space-3);
      color: white;
    }

    .hero-subtitle {
      font-size: var(--text-lg);
      color: rgba(255, 255, 255, 0.95);
      margin-bottom: var(--space-2);
    }

    .scanner-container {
      max-width: 600px;
      margin: 0 auto var(--space-6);
    }

    .tabs {
      display: flex;
      gap: var(--space-2);
      margin-bottom: var(--space-6);
    }

    .tab {
      flex: 1;
      padding: var(--space-3) var(--space-4);
      background: white;
      border: 2px solid var(--gray-300);
      color: var(--gray-700);
      border-radius: var(--radius-md);
      cursor: pointer;
      font-weight: 600;
      transition: all var(--transition-base);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--space-2);
    }

    .tab:hover {
      border-color: var(--bpjs-blue);
    }

    .tab.active {
      background: var(--bpjs-blue);
      color: white;
      border-color: var(--bpjs-blue);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .success-animation {
      display: none;
      text-align: center;
      padding: var(--space-8);
    }

    .success-animation.show {
      display: block;
      animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: scale(0.95);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .checkmark {
      width: 80px;
      height: 80px;
      margin: 0 auto var(--space-4);
      border-radius: 50%;
      background: var(--bpjs-green);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .checkmark svg {
      width: 48px;
      height: 48px;
      stroke: white;
      stroke-width: 3;
    }

    .location-preview {
      margin-top: var(--space-4);
      padding: var(--space-4);
      background: var(--gray-50);
      border-radius: var(--radius-md);
      font-size: var(--text-sm);
      display: none;
      border: 1px solid var(--gray-200);
    }

    .location-preview.show {
      display: flex;
      align-items: start;
      gap: var(--space-2);
    }

    .footer-links {
      margin-top: var(--space-6);
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: var(--space-3);
    }

    /* Override card styles for homepage services */
    .card {
      box-shadow: none !important;
      border: none !important;
    }

    .card:hover {
      box-shadow: none !important;
    }

    @media (max-width: 768px) {
      .hero-title {
        font-size: var(--text-2xl);
      }

      .logo {
        height: 40px;
      }
    }
  </style>
</head>

<body
  style="background: url('assets/images/hero-bg-building.png') no-repeat center center fixed; background-size: cover;">
  <!-- Header -->
  <div class="header" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(5px);">
    <div class="header-content">
      <div class="header-left">
        <img src="assets/images/logo-bpjs.png" alt="<?php echo htmlspecialchars($company_name); ?>" class="logo">
        <div class="logo-container">
          <div class="company-tagline"><?php echo htmlspecialchars($company_tagline); ?></div>
        </div>
      </div>
      <div class="nav">
        <!-- Links moved to main content -->
      </div>
    </div>
  </div>

  <!-- Hero -->
  <div class="hero gradient-overlay hero-pattern" style="padding-bottom: var(--space-16);">
    <h1 class="hero-title">Sistem Absensi Karyawan</h1>
    <p class="hero-subtitle"><?php echo htmlspecialchars($company_name); ?></p>
  </div>

  <div class="container" style="margin-top: -60px; position: relative; z-index: 10;">
    <!-- Main Content Card -->
    <div style="border-radius: var(--radius-xl); overflow: hidden; background: transparent;">
      <!-- Hero Section -->
      <div class="hero"
        style="background: transparent; color: var(--gray-900); padding: var(--space-16) 0; text-align: left;">
        <div class="container header-content">
          <div style="flex: 1; padding-right: var(--space-8);">
            <h1
              style="font-size: 3.5rem; color: var(--bpjs-green); margin-bottom: var(--space-4); line-height: 1.1; font-weight: 800; text-shadow: 3px 3px 0px #fff, -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff, 1px 1px 0 #fff;">
              Kerja Keras<br>
              <span
                style="color: var(--bpjs-blue); font-weight: 800; text-shadow: 3px 3px 0px #fff, -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff, 1px 1px 0 #fff;">Bebas
                Cemas</span>
            </h1>
            <p
              style="font-size: var(--text-xl); color: var(--gray-900); margin-bottom: var(--space-8); max-width: 500px; font-weight: 700; text-shadow: 2px 2px 0px #fff, -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff, 1px 1px 0 #fff;">
              Sistem Absensi Terintegrasi BPJS Ketenagakerjaan. Melindungi dan memudahkan pekerja Indonesia.
            </p>
            <div style="display: flex; gap: var(--space-4);">
              <a href="employee_login.php" class="btn btn-success btn-lg"
                style="border-radius: 50px; padding-left: 2rem; padding-right: 2rem;">
                Login Karyawan
              </a>
              <a href="admin_login.php" class="btn btn-outline btn-lg"
                style="border-radius: 50px; padding-left: 2rem; padding-right: 2rem;">
                Login Admin
              </a>
            </div>
          </div>
          <div style="flex: 1;">
            <!-- Empty space for background image -->
          </div>
        </div>
      </div>

      <!-- Services Section -->
      <div style="background: transparent; padding: var(--space-16) 0;">
        <div class="container">
          <div class="text-center" style="margin-bottom: var(--space-12);">
            <h2
              style="color: var(--bpjs-blue); margin-bottom: var(--space-2); font-weight: 800; text-shadow: 3px 3px 0px #fff, -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff, 1px 1px 0 #fff;">
              Akses Layanan</h2>
            <p class="text-gray-600"
              style="font-weight: 700; color: var(--gray-900) !important; text-shadow: 2px 2px 0px #fff, -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff, 1px 1px 0 #fff;">
              Pilih portal yang sesuai dengan kebutuhan Anda</p>
          </div>

          <div class="grid grid-cols-2 gap-4" style="max-width: 900px; margin: 0 auto;">
            <!-- Employee Card -->
            <a href="employee_login.php" class="card"
              style="text-decoration: none; color: inherit; transition: all 0.3s; border: none; box-shadow: none; background: white; display: flex; align-items: center; gap: var(--space-6); padding: var(--space-8); border-radius: var(--radius-lg);">
              <div
                style="background: var(--primary-50); padding: var(--space-4); border-radius: 50%; color: var(--bpjs-blue); display: flex; align-items: center; justify-content: center;">
                <div style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                  <?php echo SVGIcons::user(); ?>
                </div>
              </div>
              <div>
                <h3 style="color: var(--bpjs-blue); margin-bottom: var(--space-2); font-size: var(--text-xl);">Portal
                  Karyawan</h3>
                <p class="text-gray-600" style="margin: 0; font-size: var(--text-base);">Absensi, Riwayat Kehadiran,
                  dan Pengajuan Izin.</p>
              </div>
              <div style="margin-left: auto; color: var(--gray-400);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
              </div>
            </a>

            <!-- Admin Card -->
            <a href="admin_login.php" class="card"
              style="text-decoration: none; color: inherit; transition: all 0.3s; border: none; box-shadow: none; background: white; display: flex; align-items: center; gap: var(--space-6); padding: var(--space-8); border-radius: var(--radius-lg);">
              <div
                style="background: var(--secondary-50); padding: var(--space-4); border-radius: 50%; color: var(--bpjs-green); display: flex; align-items: center; justify-content: center;">
                <div style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                  <?php echo SVGIcons::shield(); ?>
                </div>
              </div>
              <div>
                <h3 style="color: var(--bpjs-green); margin-bottom: var(--space-2); font-size: var(--text-xl);">Sistem
                  Admin</h3>
                <p class="text-gray-600" style="margin: 0; font-size: var(--text-base);">Dashboard Monitoring,
                  Laporan, dan Manajemen Data.</p>
              </div>
              <div style="margin-left: auto; color: var(--gray-400);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
              </div>
            </a>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer style="background: var(--bpjs-blue-dark); color: white; padding: var(--space-8) 0; text-align: center;">
        <div class="container">
          <p style="opacity: 0.8; margin-bottom: var(--space-2);">&copy; <?php echo date('Y'); ?>
            <?php echo htmlspecialchars($company_name); ?>. Hak Cipta Dilindungi.
          </p>
          <div
            style="display: flex; justify-content: center; gap: var(--space-4); opacity: 0.8; font-size: var(--text-sm);">
            <a href="privacy-policy.php" style="color: white; text-decoration: none; transition: opacity 0.3s;"
              onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">Kebijakan Privasi</a>
            <span style="opacity: 0.5;">•</span>
            <a href="terms-conditions.php" style="color: white; text-decoration: none; transition: opacity 0.3s;"
              onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">Syarat & Ketentuan</a>
            <span style="opacity: 0.5;">•</span>
            <a href="contact.php" style="color: white; text-decoration: none; transition: opacity 0.3s;"
              onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">Hubungi Kami</a>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <script>
    // No specific JS needed for this landing page
  </script>
</body>

</html>