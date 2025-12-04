<?php
session_start();
require_once 'db.php';
require_once 'csrf.php';
require_once 'includes/icons.php';

if (!isset($_SESSION['emp_id'])) {
  header('Location: employee_login.php');
  exit;
}

$mysqli = db_connect();
$csrf = csrf_token();
$emp_id = (int) $_SESSION['emp_id'];
$emp_code = $_SESSION['emp_code'];
$emp_name = $_SESSION['emp_name'];

// Get settings
$settings_query = $mysqli->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $settings_query->fetch_assoc()) {
  $settings[$row['setting_key']] = $row['setting_value'];
}

$company_name = $settings['company_name'] ?? 'BPJS Ketenagakerjaan';

// Get employee info
$stmt = $mysqli->prepare("SELECT e.*, d.name as dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = ?");
$stmt->bind_param('i', $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Get attendance history
$stmt = $mysqli->prepare("SELECT * FROM attendances WHERE employee_id = ? ORDER BY created_at DESC LIMIT 30");
$stmt->bind_param('i', $emp_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get today's statistics
$today = date('Y-m-d');
$stmt = $mysqli->prepare("SELECT type, COUNT(*) as count FROM attendances WHERE employee_id = ? AND DATE(created_at) = ? GROUP BY type");
$stmt->bind_param('is', $emp_id, $today);
$stmt->execute();
$today_stats = [];
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $today_stats[$row['type']] = $row['count'];
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf); ?>">
  <meta name="description" content="Portal Karyawan - <?php echo htmlspecialchars($company_name); ?>">
  <title>Portal Karyawan - <?php echo htmlspecialchars($emp_name); ?></title>
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
    }

    .navbar-brand-text {
      font-size: var(--text-xl);
      font-weight: 700;
      color: var(--gray-900);
    }

    .navbar-brand svg {
      width: 24px;
      height: 24px;
      color: var(--bpjs-blue);
    }

    .welcome-card {
      background: linear-gradient(135deg, var(--bpjs-blue) 0%, var(--bpjs-blue-dark) 100%);
      color: white;
      padding: var(--space-8);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-xl);
      margin-bottom: var(--space-6);
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .welcome-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -20%;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      border-radius: 50%;
    }

    .welcome-title {
      font-size: var(--text-3xl);
      font-weight: 800;
      margin-bottom: var(--space-2);
      position: relative;
      z-index: 1;
    }

    .employee-code {
      font-family: var(--font-mono);
      background: rgba(255, 255, 255, 0.2);
      padding: var(--space-2) var(--space-4);
      border-radius: var(--radius-md);
      display: inline-block;
      font-weight: 600;
      position: relative;
      z-index: 1;
    }

    .dept-badge {
      display: inline-flex;
      align-items: center;
      gap: var(--space-2);
      padding: var(--space-2) var(--space-3);
      background: rgba(255, 255, 255, 0.15);
      border-radius: var(--radius-md);
      margin-top: var(--space-3);
      position: relative;
      z-index: 1;
    }

    .dept-badge svg {
      width: 16px;
      height: 16px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: var(--space-4);
      margin-bottom: var(--space-6);
    }

    .stat-card {
      background: white;
      padding: var(--space-6);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-md);
      border-left: 4px solid;
    }

    .stat-card:nth-child(1) {
      border-color: var(--bpjs-blue);
    }

    .stat-card:nth-child(2) {
      border-color: var(--bpjs-green);
    }

    .stat-card:nth-child(3) {
      border-color: var(--warning);
    }

    .quick-action-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: var(--space-4);
      margin-bottom: var(--space-6);
    }

    .quick-action {
      background: white;
      padding: var(--space-6);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-md);
      text-align: center;
      cursor: pointer;
      transition: all var(--transition-base);
      border: 2px solid transparent;
    }

    .quick-action:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-lg);
      border-color: var(--bpjs-blue);
    }

    .action-icon {
      width: 56px;
      height: 56px;
      margin: 0 auto var(--space-3);
      background: var(--primary-50);
      border-radius: var(--radius-lg);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .action-icon svg {
      width: 28px;
      height: 28px;
      color: var(--bpjs-blue);
    }

    .action-label {
      font-weight: 600;
      color: var(--gray-900);
    }

    .section-title {
      display: flex;
      align-items: center;
      gap: var(--space-2);
      margin-bottom: var(--space-4);
    }

    .quick-action.active {
      border-color: var(--bpjs-blue);
      background: var(--primary-50);
      box-shadow: var(--shadow-md);
    }

    .section-title svg {
      width: 24px;
      height: 24px;
      color: var(--bpjs-blue);
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-content">
      <div class="navbar-brand">
        <img src="assets/images/logo-bpjs.png" alt="BPJS" style="height: 40px;">
        <span class="navbar-brand-text">Portal Karyawan</span>
      </div>
      <a href="employee_logout.php" class="btn btn-sm btn-outline">
        <?php echo SVGIcons::logout(); ?>
        <span>Logout</span>
      </a>
    </div>
  </nav>

  <div class="container">
    <!-- Welcome Card -->
    <div class="welcome-card">
      <h1 class="welcome-title">Selamat Datang, <?php echo htmlspecialchars($emp_name); ?>!</h1>
      <p><span class="employee-code"><?php echo htmlspecialchars($emp_code); ?></span></p>
      <?php if ($employee['dept_name']): ?>
        <div class="dept-badge">
          <?php echo SVGIcons::briefcase(); ?>
          <span><?php echo htmlspecialchars($employee['dept_name']); ?></span>
        </div>
      <?php endif; ?>
    </div>

    <!-- My QR Code Section -->
    <div class="card mb-6">
      <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="section-title" style="margin-bottom: 0;">
          <?php echo SVGIcons::qrcode(); ?>
          <span>QR Code Saya</span>
        </h3>
        <button onclick="toggleQRCode()" class="btn btn-sm btn-outline">
          <?php echo SVGIcons::chevronDown(); ?> Tampilkan/Sembunyikan
        </button>
      </div>
      <div id="my-qr-code" class="text-center" style="display: none;">
        <div
          style="background: white; padding: var(--space-4); display: inline-block; border-radius: var(--radius-lg); border: 1px solid var(--gray-200);">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode($emp_code); ?>"
            alt="QR Code <?php echo htmlspecialchars($emp_name); ?>" style="width: 200px; height: 200px;">
          <div
            style="margin-top: var(--space-2); font-weight: bold; color: var(--gray-900); font-family: var(--font-mono);">
            <?php echo htmlspecialchars($emp_code); ?>
          </div>
        </div>
        <p class="text-gray-600 mt-3 text-sm">Tunjukkan QR Code ini kepada Admin atau Scanner Kantor untuk absensi.</p>
      </div>
    </div>

    <!-- Today's Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-value"><?php echo $today_stats['checkin'] ?? 0; ?></div>
        <div class="stat-label">Check-in Hari Ini</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $today_stats['checkout'] ?? 0; ?></div>
        <div class="stat-label">Check-out Hari Ini</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">
          <?php echo ($today_stats['izin'] ?? 0) + ($today_stats['sakit'] ?? 0) + ($today_stats['cuti'] ?? 0); ?>
        </div>
        <div class="stat-label">Izin Hari Ini</div>
      </div>
    </div>

    <!-- QR Scanner Section -->
    <div class="card mb-6">
      <div class="card-header">
        <h3 class="section-title" style="margin-bottom: 0;">
          <?php echo SVGIcons::qrcode(); ?>
          <span>Scan QR Code</span>
        </h3>
      </div>
      <div class="text-center">
        <div id="qr-reader" style="display:none; margin: 0 auto var(--space-4); max-width: 400px;"></div>

        <div id="scanner-placeholder" class="text-center"
          style="padding: var(--space-8); background: var(--gray-50); border-radius: var(--radius-lg); margin-bottom: var(--space-4); border: 2px dashed var(--gray-200);">
          <div style="color: var(--gray-400); margin-bottom: var(--space-2);">
            <?php echo SVGIcons::camera(); ?>
          </div>
          <p class="text-gray-500 text-sm">Kamera belum aktif</p>
        </div>

        <button id="startScanner" class="btn btn-primary" style="width:100%; max-width: 300px;">
          <?php echo SVGIcons::camera(); ?>
          <span>Mulai Scan QR</span>
        </button>
        <button id="stopScanner" class="btn btn-danger" style="width:100%; max-width: 300px; display:none;">
          <?php echo SVGIcons::alertCircle(); ?>
          <span>Stop Kamera</span>
        </button>
        <p class="text-gray-500 text-sm mt-3">Gunakan ini untuk scan QR Code lokasi/kantor jika diperlukan.</p>
      </div>
    </div>

    <!-- Absensi Section (Merged) -->
    <div class="card mb-6">
      <div class="card-header">
        <h3 class="section-title" style="margin-bottom: 0;">
          <?php echo SVGIcons::clipboard(); ?>
          <span>Absensi</span>
        </h3>
      </div>

      <form id="attendanceForm">
        <input type="hidden" id="type" value="checkin">

        <div class="form-group">
          <label class="form-label">Pilih Jenis Absensi</label>
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="quick-action active" onclick="selectType('checkin', this)">
              <div class="action-icon">
                <?php echo SVGIcons::checkCircle(); ?>
              </div>
              <div class="action-label">Check-in</div>
            </div>
            <div class="quick-action" onclick="selectType('checkout', this)">
              <div class="action-icon">
                <?php echo SVGIcons::logOut(); ?>
              </div>
              <div class="action-label">Check-out</div>
            </div>
            <div class="quick-action" onclick="selectType('izin', this)">
              <div class="action-icon">
                <?php echo SVGIcons::file(); ?>
              </div>
              <div class="action-label">Izin</div>
            </div>
            <div class="quick-action" onclick="selectType('sakit', this)">
              <div class="action-icon">
                <?php echo SVGIcons::alertCircle(); ?>
              </div>
              <div class="action-label">Sakit</div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Keterangan <span class="text-gray-500">(Opsional)</span></label>
          <textarea id="note" class="form-textarea" placeholder="Masukkan keterangan (jika ada)..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Foto <span class="text-gray-500">(Opsional)</span></label>
          <input type="file" id="photo" class="form-file" accept="image/*" capture="environment">
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width:100%">
          <span class="spinner" id="submitSpinner" style="display:none"></span>
          <?php echo SVGIcons::upload(); ?>
          <span id="submitText">Kirim Absensi</span>
        </button>
      </form>

      <div id="statusMessage" style="margin-top: var(--space-4)"></div>
    </div>

    <!-- Attendance History -->
    <div class="card">
      <div class="card-header">
        <h3 class="section-title" style="margin-bottom: 0;">
          <?php echo SVGIcons::list(); ?>
          <span>Riwayat Absensi (30 Terakhir)</span>
        </h3>
      </div>

      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Jenis</th>
              <th>Status</th>
              <th>Keterangan</th>
              <th>Lokasi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="5" class="text-center text-gray-500">Belum ada riwayat absensi.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td><?php echo date('d M Y H:i', strtotime($r['created_at'])); ?></td>
                  <td>
                    <span class="badge badge-<?php
                    echo $r['type'] === 'checkin' ? 'primary' :
                      ($r['type'] === 'checkout' ? 'success' : 'warning');
                    ?>">
                      <?php echo strtoupper($r['type']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-<?php
                    echo $r['status'] === 'on_time' ? 'success' :
                      ($r['status'] === 'permit' ? 'info' : 'danger');
                    ?>">
                      <?php echo str_replace('_', ' ', strtoupper($r['status'])); ?>
                    </span>
                  </td>
                  <td><?php echo htmlspecialchars($r['note'] ?? '-'); ?></td>
                  <td>
                    <?php if ($r['lat'] && $r['lon']): ?>
                      <?php echo number_format($r['lat'], 4); ?>, <?php echo number_format($r['lon'], 4); ?>
                    <?php else: ?>
                      <span class="text-gray-400">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const empCode = <?php echo json_encode($emp_code); ?>;

    // QR Scanner Logic
    let html5QrCode = null;
    const qrReader = document.getElementById('qr-reader');
    const startBtn = document.getElementById('startScanner');
    const stopBtn = document.getElementById('stopScanner');

    if (startBtn) {
      startBtn.addEventListener('click', async () => {
        try {
          document.getElementById('scanner-placeholder').style.display = 'none';
          qrReader.style.display = 'block';
          startBtn.style.display = 'none';
          stopBtn.style.display = 'inline-flex';

          html5QrCode = new Html5Qrcode("qr-reader");

          await html5QrCode.start(
            { facingMode: "environment" },
            {
              fps: 10,
              qrbox: { width: 250, height: 250 }
            },
            onScanSuccess
          );
        } catch (err) {
          showMessage('Gagal mengakses kamera: ' + err, 'danger');
          resetScanner();
        }
      });

      stopBtn.addEventListener('click', async () => {
        await stopScanner();
      });
    }

    async function stopScanner() {
      if (html5QrCode) {
        try {
          await html5QrCode.stop();
          html5QrCode.clear();
        } catch (e) {
          console.log("Error stopping scanner", e);
        }
      }
      resetScanner();
    }

    function resetScanner() {
      document.getElementById('scanner-placeholder').style.display = 'block';
      qrReader.style.display = 'none';
      startBtn.style.display = 'inline-flex';
      stopBtn.style.display = 'none';
    }

    function onScanSuccess(decodedText) {
      stopScanner();
      showMessage('QR Code terdeteksi! Memproses...', 'info');
      // Assuming QR contains location or just triggers checkin
      // For now, we'll just treat it as a check-in trigger
      submitAttendance('checkin', 'Via QR Code: ' + decodedText);
    }

    // Select Type Logic
    function selectType(type, element) {
      // Update hidden input
      document.getElementById('type').value = type;

      // Update visual state
      document.querySelectorAll('.quick-action').forEach(el => el.classList.remove('active'));
      element.classList.add('active');
    }

    // Form submission
    document.getElementById('attendanceForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const type = document.getElementById('type').value;
      const note = document.getElementById('note').value.trim();
      const photo = document.getElementById('photo').files[0];

      const submitBtn = document.querySelector('#attendanceForm button[type="submit"]');
      const submitSpinner = document.getElementById('submitSpinner');
      const submitText = document.getElementById('submitText');

      submitBtn.disabled = true;
      submitSpinner.style.display = 'inline-block';
      submitText.textContent = 'Mengirim...';

      await submitAttendance(type, note, photo);

      submitBtn.disabled = false;
      submitSpinner.style.display = 'none';
      submitText.textContent = 'Kirim Absensi';

      document.getElementById('attendanceForm').reset();
    });

    async function submitAttendance(type, note, photo = null) {
      try {
        let lat = null, lon = null;

        if (navigator.geolocation) {
          try {
            const position = await new Promise((resolve, reject) => {
              navigator.geolocation.getCurrentPosition(resolve, reject, {
                timeout: 5000,
                enableHighAccuracy: true
              });
            });
            lat = position.coords.latitude;
            lon = position.coords.longitude;
          } catch (err) {
            console.log('Geolocation error:', err);
          }
        }

        const formData = new FormData();
        formData.append('employee_code', empCode);
        formData.append('type', type);
        formData.append('csrf_token', csrfToken);

        if (lat && lon) {
          formData.append('lat', lat);
          formData.append('lon', lon);
        }

        if (note) {
          formData.append('note', note);
        }

        if (photo) {
          formData.append('photo', photo);
        }

        const response = await fetch('checkin.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          showMessage(result.message, 'success');
          setTimeout(() => location.reload(), 1500);
        } else {
          showMessage(result.message || 'Gagal', 'danger');
        }
      } catch (error) {
        showMessage('Error: ' + error.message, 'danger');
      }
    }

    function showMessage(message, type = 'info') {
      const statusDiv = document.getElementById('statusMessage');
      statusDiv.className = `alert alert-${type}`;

      const iconMap = {
        'success': '<?php echo addslashes(SVGIcons::checkCircle()); ?>',
        'danger': '<?php echo addslashes(SVGIcons::alertTriangle()); ?>',
        'info': '<?php echo addslashes(SVGIcons::info()); ?>'
      };

      statusDiv.innerHTML = `
        <div style="display: flex; align-items: start; gap: var(--space-2);">
          ${iconMap[type] || iconMap['info']}
          <span>${message}</span>
        </div>
      `;

      setTimeout(() => {
        statusDiv.className = '';
        statusDiv.textContent = '';
      }, 5000);
    }
    // Toggle QR Code
    function toggleQRCode() {
      const qrDiv = document.getElementById('my-qr-code');
      if (qrDiv.style.display === 'none') {
        qrDiv.style.display = 'block';
      } else {
        qrDiv.style.display = 'none';
      }
    }
  </script>
</body>

</html>