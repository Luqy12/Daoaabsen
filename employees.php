<?php
session_start();
require_once 'db.php';
require_once 'csrf.php';
require_once 'includes/icons.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: admin_login.php');
  exit;
}

$mysqli = db_connect();
$msg = '';
$msg_type = 'success';
$csrf = csrf_token();

// Handle POST requests (Add, Edit, Delete, Generate QR)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf_token'] ?? '';
  if (!csrf_check($token)) {
    $msg = 'CSRF token tidak valid. Silakan reload halaman.';
    $msg_type = 'danger';
  } else {
    $action = $_POST['action'] ?? '';

    // ADD Employee
    if ($action === 'add') {
      $code = trim($_POST['employee_code'] ?? '');
      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $phone = trim($_POST['phone'] ?? '');
      $password = trim($_POST['password'] ?? '');
      $department_id = $_POST['department_id'] ?? null;
      $status = $_POST['status'] ?? 'active';

      if ($code && $name) {
        $password_hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        $stmt = $mysqli->prepare('INSERT INTO employees (employee_code, name, email, phone, password, department_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssis', $code, $name, $email, $phone, $password_hash, $department_id, $status);

        if ($stmt->execute()) {
          $msg = "Karyawan berhasil ditambahkan!";
          $msg_type = 'success';
        } else {
          $msg = 'Gagal menambahkan karyawan: ' . $mysqli->error;
          $msg_type = 'danger';
        }
      } else {
        $msg = 'Kode dan nama karyawan harus diisi!';
        $msg_type = 'danger';
      }
    }

    // GENERATE QR Code
    elseif ($action === 'generate_qr') {
      $employee_id = $_POST['employee_id'] ?? 0;
      if ($employee_id) {
        // Check if employee exists
        $stmt = $mysqli->prepare('SELECT id, employee_code, name FROM employees WHERE id = ?');
        $stmt->bind_param('i', $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
          $employee = $result->fetch_assoc();
          $employee_code = $employee['employee_code'];

          // Generate QR code using qrserver.com API (free and working)
          $qr_data = urlencode($employee_code);
          $qr_filename = "qrcodes/qr_" . $employee_code . ".png";
          $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . $qr_data;

          // Download QR code image
          $qr_image = file_get_contents($qr_url);
          if ($qr_image !== false) {
            file_put_contents($qr_filename, $qr_image);

            // Update database
            $update_stmt = $mysqli->prepare('UPDATE employees SET qr_code = ? WHERE id = ?');
            $update_stmt->bind_param('si', $qr_filename, $employee_id);
            $update_stmt->execute();

            $msg = "QR Code berhasil di-generate untuk " . htmlspecialchars($employee['name']) . "!";
            $msg_type = 'success';
          } else {
            $msg = 'Gagal mengunduh QR Code dari API.';
            $msg_type = 'danger';
          }
        }
      }
    }

    // DELETE Employee
    elseif ($action === 'delete') {
      $employee_id = $_POST['employee_id'] ?? 0;
      if ($employee_id) {
        // Get QR code path first to delete file
        $stmt = $mysqli->prepare('SELECT qr_code FROM employees WHERE id = ?');
        $stmt->bind_param('i', $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
          $emp = $result->fetch_assoc();
          if ($emp['qr_code'] && file_exists($emp['qr_code'])) {
            unlink($emp['qr_code']);
          }
        }

        // Delete employee
        $delete_stmt = $mysqli->prepare('DELETE FROM employees WHERE id = ?');
        $delete_stmt->bind_param('i', $employee_id);
        if ($delete_stmt->execute()) {
          $msg = 'Karyawan berhasil dihapus!';
          $msg_type = 'success';
        } else {
          $msg = 'Gagal menghapus karyawan: ' . $mysqli->error;
          $msg_type = 'danger';
        }
      }
    }
  }
}

// Get departments for dropdown
$departments = $mysqli->query('SELECT id, name FROM departments ORDER BY name')->fetch_all(MYSQLI_ASSOC);

// Get employees with department info
$employees = $mysqli->query('
    SELECT e.*, d.name as department_name 
    FROM employees e 
    LEFT JOIN departments d ON e.department_id = d.id 
    ORDER BY e.created_at DESC
')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Karyawan - Sistem Absensi</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: var(--space-6);
    }

    .employee-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: var(--space-4);
      margin-top: var(--space-6);
    }

    .employee-card {
      background: white;
      border-radius: var(--radius-lg);
      padding: var(--space-4);
      box-shadow: var(--shadow-md);
      transition: all var(--transition-base);
    }

    .employee-card:hover {
      box-shadow: var(--shadow-xl);
      transform: translateY(-2px);
    }

    .employee-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: var(--space-3);
    }

    .employee-code {
      font-family: var(--font-mono);
      font-size: var(--text-sm);
      color: var(--primary-600);
      font-weight: 600;
    }

    .employee-name {
      font-size: var(--text-lg);
      font-weight: 700;
      color: var(--gray-900);
      margin-bottom: var(--space-1);
    }

    .employee-info {
      font-size: var(--text-sm);
      color: var(--gray-600);
      margin-bottom: var(--space-2);
    }

    .qr-preview {
      text-align: center;
      margin: var(--space-3) 0;
      padding: var(--space-3);
      background: var(--gray-50);
      border-radius: var(--radius-md);
    }

    .qr-preview img {
      max-width: 150px;
      height: auto;
      border: 2px solid var(--gray-300);
      border-radius: var(--radius-md);
    }

    .employee-actions {
      display: flex;
      gap: var(--space-2);
      margin-top: var(--space-3);
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
    }

    .modal.show {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background-color: white;
      padding: var(--space-6);
      border-radius: var(--radius-xl);
      max-width: 500px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: var(--shadow-2xl);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: var(--space-4);
    }

    .modal-title {
      font-size: var(--text-2xl);
      font-weight: 700;
    }

    .close {
      font-size: 28px;
      font-weight: bold;
      color: var(--gray-500);
      cursor: pointer;
      line-height: 1;
    }

    .close:hover {
      color: var(--gray-900);
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="top-bar">
      <div>
        <h1><?php echo SVGIcons::users(); ?> Kelola Karyawan</h1>
        <p class="text-gray-600">Manajemen data karyawan dan QR Code</p>
      </div>
      <div class="flex gap-3">
        <button onclick="openAddModal()" class="btn btn-primary"><?php echo SVGIcons::plus(); ?> Tambah
          Karyawan</button>
        <a href="dashboard.php" class="btn btn-outline">← Kembali</a>
      </div>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-<?php echo $msg_type; ?>">
        <?php echo htmlspecialchars($msg); ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Daftar Karyawan (<?php echo count($employees); ?>)</h2>
      </div>

      <div class="employee-grid">
        <?php foreach ($employees as $emp): ?>
          <div class="employee-card">
            <div class="employee-header">
              <div>
                <div class="employee-code"><?php echo SVGIcons::idCard(); ?>
                  <?php echo htmlspecialchars($emp['employee_code']); ?></div>
                <div class="employee-name"><?php echo htmlspecialchars($emp['name']); ?></div>
              </div>
              <span class="badge badge-<?php echo $emp['status'] === 'active' ? 'success' : 'secondary'; ?>">
                <?php echo ucfirst($emp['status']); ?>
              </span>
            </div>

            <div class="employee-info">
              <?php if ($emp['email']): ?>
                <div><?php echo SVGIcons::mail(); ?>     <?php echo htmlspecialchars($emp['email']); ?></div>
              <?php endif; ?>
              <?php if ($emp['phone']): ?>
                <div><?php echo SVGIcons::phone(); ?>     <?php echo htmlspecialchars($emp['phone']); ?></div>
              <?php endif; ?>
              <?php if ($emp['department_name']): ?>
                <div><?php echo SVGIcons::building(); ?>     <?php echo htmlspecialchars($emp['department_name']); ?></div>
              <?php endif; ?>
            </div>

            <?php if ($emp['qr_code'] && file_exists($emp['qr_code'])): ?>
              <div class="qr-preview">
                <img src="<?php echo htmlspecialchars($emp['qr_code']); ?>" alt="QR Code">
                <div class="text-xs text-gray-500 mt-2">QR Code tersedia</div>
              </div>
            <?php else: ?>
              <div class="text-center py-4 text-gray-500">
                <div class="text-2xl mb-2"><?php echo SVGIcons::qrcode(); ?></div>
                <div class="text-sm">QR Code belum di-generate</div>
              </div>
            <?php endif; ?>

            <div class="employee-actions">
              <form method="POST" style="flex: 1;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="action" value="generate_qr">
                <input type="hidden" name="employee_id" value="<?php echo $emp['id']; ?>">
                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                  <?php echo SVGIcons::refresh(); ?>   <?php echo $emp['qr_code'] ? 'Re-generate' : 'Generate'; ?> QR
                </button>
              </form>

              <?php if ($emp['qr_code'] && file_exists($emp['qr_code'])): ?>
                <a href="<?php echo htmlspecialchars($emp['qr_code']); ?>" download class="btn btn-success btn-sm">
                  <?php echo SVGIcons::save(); ?>
                </a>
              <?php endif; ?>

              <form method="POST" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?');">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="employee_id" value="<?php echo $emp['id']; ?>">
                <button type="submit" class="btn btn-danger btn-sm"><?php echo SVGIcons::trash(); ?></button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Add Employee Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Tambah Karyawan Baru</h2>
        <span class="close" onclick="closeAddModal()">&times;</span>
      </div>

      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="action" value="add">

        <div class="form-group">
          <label class="form-label">Kode Karyawan *</label>
          <input type="text" name="employee_code" class="form-input" placeholder="Contoh: EMP001" required>
        </div>

        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" name="name" class="form-input" placeholder="Nama karyawan" required>
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-input" placeholder="email@example.com">
        </div>

        <div class="form-group">
          <label class="form-label">Telepon</label>
          <input type="tel" name="phone" class="form-input" placeholder="08123456789">
        </div>

        <div class="form-group">
          <label class="form-label">Departemen</label>
          <select name="department_id" class="form-select">
            <option value="">-- Pilih Departemen --</option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?php echo $dept['id']; ?>">
                <?php echo htmlspecialchars($dept['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Password (untuk login portal karyawan)</label>
          <input type="password" name="password" class="form-input" placeholder="Password opsional">
        </div>

        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>

        <div class="flex gap-3">
          <button type="submit" class="btn btn-primary" style="flex: 1;">
            <?php echo SVGIcons::plus(); ?> Tambah Karyawan
          </button>
          <button type="button" onclick="closeAddModal()" class="btn btn-outline">
            Batal
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openAddModal() {
      document.getElementById('addModal').classList.add('show');
    }

    function closeAddModal() {
      document.getElementById('addModal').classList.remove('show');
    }

    // Close modal when clicking outside
    window.onclick = function (event) {
      const modal = document.getElementById('addModal');
      if (event.target === modal) {
        closeAddModal();
      }
    }
  </script>
</body>

</html>