<?php
session_start();
require_once 'db.php';
require_once 'includes/icons.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: admin_login.php');
  exit;
}

$mysqli = db_connect();

// Get filter parameters
$filter_date = $_GET['date'] ?? date('Y-m-d');
$filter_type = $_GET['type'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_clauses = ["DATE(a.created_at) = ?"];
$params = [$filter_date];
$types = 's';

if ($filter_type) {
  $where_clauses[] = "a.type = ?";
  $params[] = $filter_type;
  $types .= 's';
}

if ($filter_status) {
  $where_clauses[] = "a.status = ?";
  $params[] = $filter_status;
  $types .= 's';
}

if ($search) {
  $where_clauses[] = "(e.name LIKE ? OR e.employee_code LIKE ?)";
  $search_param = "%{$search}%";
  $params[] = $search_param;
  $params[] = $search_param;
  $types .= 'ss';
}

$where_sql = implode(' AND ', $where_clauses);

// Fetch attendances with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

$count_query = "SELECT COUNT(*) as total FROM attendances a 
                JOIN employees e ON a.employee_id = e.id 
                WHERE $where_sql";

$stmt = $mysqli->prepare($count_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);

$query = "SELECT a.*, e.employee_code, e.name, e.photo, d.name as dept_name 
          FROM attendances a 
          JOIN employees e ON a.employee_id = e.id 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE $where_sql
          ORDER BY a.created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$attendances = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Absensi - Sistem Absensi</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    body {
      background: var(--gray-100);
    }

    .navbar {
      background: white;
      padding: var(--space-4) var(--space-6);
      box-shadow: var(--shadow);
      margin-bottom: var(--space-6);
    }

    .navbar-content {
      max-width: 1400px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar-brand {
      font-size: var(--text-xl);
      font-weight: 800;
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .filters {
      background: white;
      padding: var(--space-6);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow);
      margin-bottom: var(--space-6);
    }

    .filter-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: var(--space-4);
      align-items: end;
    }

    .pagination {
      display: flex;
      justify-content: center;
      gap: var(--space-2);
      margin-top: var(--space-6);
    }

    .pagination a,
    .pagination span {
      padding: var(--space-2) var(--space-4);
      border-radius: var(--radius);
      text-decoration: none;
      color: var(--gray-700);
      background: white;
      border: 1px solid var(--gray-300);
    }

    .pagination a:hover {
      background: var(--primary-50);
      border-color: var(--primary-500);
    }

    .pagination .active {
      background: var(--gradient-primary);
      color: white;
      border-color: transparent;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal.show {
      display: flex;
    }

    .modal-content {
      background: white;
      padding: var(--space-6);
      border-radius: var(--radius-xl);
      max-width: 800px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
    }

    #detailMap {
      height: 300px;
      border-radius: var(--radius-lg);
      margin: var(--space-4) 0;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-content">
      <div class="navbar-brand"><?php echo SVGIcons::barChart(); ?> Data Absensi</div>
      <div class="flex gap-4">
        <a href="dashboard.php" class="btn btn-sm btn-secondary"><?php echo SVGIcons::home(); ?> Dashboard</a>
        <a href="logout.php" class="btn btn-sm btn-secondary"><?php echo SVGIcons::logout(); ?> Logout</a>
      </div>
    </div>
  </nav>

  <div class="container-lg">
    <!-- Filters -->
    <div class="filters">
      <h3 class="mb-4"><?php echo SVGIcons::search(); ?> Filter & Pencarian</h3>
      <form method="GET" action="">
        <div class="filter-row">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Tanggal</label>
            <input type="date" name="date" class="form-input" value="<?php echo htmlspecialchars($filter_date); ?>">
          </div>

          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Jenis</label>
            <select name="type" class="form-select">
              <option value="">Semua</option>
              <option value="checkin" <?php echo $filter_type === 'checkin' ? 'selected' : ''; ?>>Check-in</option>
              <option value="checkout" <?php echo $filter_type === 'checkout' ? 'selected' : ''; ?>>Check-out</option>
              <option value="izin" <?php echo $filter_type === 'izin' ? 'selected' : ''; ?>>Izin</option>
              <option value="sakit" <?php echo $filter_type === 'sakit' ? 'selected' : ''; ?>>Sakit</option>
              <option value="cuti" <?php echo $filter_type === 'cuti' ? 'selected' : ''; ?>>Cuti</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">Semua</option>
              <option value="on_time" <?php echo $filter_status === 'on_time' ? 'selected' : ''; ?>>Tepat Waktu</option>
              <option value="late" <?php echo $filter_status === 'late' ? 'selected' : ''; ?>>Terlambat</option>
              <option value="very_late" <?php echo $filter_status === 'very_late' ? 'selected' : ''; ?>>Sangat Terlambat
              </option>
              <option value="permit" <?php echo $filter_status === 'permit' ? 'selected' : ''; ?>>Izin</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Cari Karyawan</label>
            <input type="text" name="search" class="form-input" placeholder="Nama atau kode..."
              value="<?php echo htmlspecialchars($search); ?>">
          </div>

          <div>
            <button type="submit" class="btn btn-primary"><?php echo SVGIcons::search(); ?> Filter</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Results -->
    <div class="card">
      <div class="card-header flex justify-between items-center">
        <h3 class="card-title">Hasil: <?php echo $total_records; ?> Data</h3>
        <a href="export.php?<?php echo http_build_query($_GET); ?>"
          class="btn btn-success"><?php echo SVGIcons::download(); ?> Export Excel</a>
      </div>

      <div class="table-wrapper">
        <table class="table">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Kode</th>
              <th>Nama</th>
              <th>Departemen</th>
              <th>Jenis</th>
              <th>Status</th>
              <th>Lokasi</th>
              <th>Keterangan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($attendances)): ?>
              <tr>
                <td colspan="9" class="text-center text-gray-500">Tidak ada data untuk tanggal ini.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($attendances as $att): ?>
                <tr>
                  <td><?php echo date('H:i', strtotime($att['created_at'])); ?></td>
                  <td><strong><?php echo htmlspecialchars($att['employee_code']); ?></strong></td>
                  <td><?php echo htmlspecialchars($att['name']); ?></td>
                  <td><?php echo htmlspecialchars($att['dept_name'] ?? '-'); ?></td>
                  <td>
                    <span class="badge badge-<?php
                    echo $att['type'] === 'checkin' ? 'info' :
                      ($att['type'] === 'checkout' ? 'success' : 'warning');
                    ?>">
                      <?php echo strtoupper($att['type']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-<?php
                    echo $att['status'] === 'on_time' ? 'success' :
                      ($att['status'] === 'permit' ? 'info' : 'danger');
                    ?>">
                      <?php echo str_replace('_', ' ', strtoupper($att['status'])); ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($att['lat'] && $att['lon']): ?>
                      <button
                        onclick="showMap(<?php echo $att['id']; ?>, <?php echo $att['lat']; ?>, <?php echo $att['lon']; ?>, '<?php echo htmlspecialchars($att['name']); ?>')"
                        class="btn btn-sm btn-outline"><?php echo SVGIcons::mapPin(); ?> Peta</button>
                      <?php if ($att['distance_from_office']): ?>
                        <br><small><?php echo round($att['distance_from_office']); ?>m</small>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-gray-400">-</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($att['note'] ?? '-'); ?></td>
                  <td>
                    <button onclick="showDetail(<?php echo $att['id']; ?>)"
                      class="btn btn-sm btn-primary"><?php echo SVGIcons::eye(); ?>
                      Detail</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <div class="pagination">
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php
            $url_params = $_GET;
            $url_params['page'] = $i;
            $url = '?' . http_build_query($url_params);
            ?>
            <?php if ($i === $page): ?>
              <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
              <a href="<?php echo $url; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Map Modal -->
  <div id="mapModal" class="modal">
    <div class="modal-content">
      <div class="flex justify-between items-center mb-4">
        <h3><?php echo SVGIcons::mapPin(); ?> Lokasi Absensi</h3>
        <button onclick="closeModal()" class="btn btn-sm btn-secondary"><?php echo SVGIcons::close(); ?> Tutup</button>
      </div>
      <div id="detailMap"></div>
      <p id="mapInfo" class="mt-3 text-center text-gray-600"></p>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    let map = null;

    function showMap(id, lat, lon, name) {
      const modal = document.getElementById('mapModal');
      modal.classList.add('show');

      setTimeout(() => {
        if (map) {
          map.remove();
        }

        map = L.map('detailMap').setView([lat, lon], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lon]).addTo(map)
          .bindPopup(`<strong>${name}</strong><br>Lat: ${lat}<br>Lon: ${lon}`)
          .openPopup();

        document.getElementById('mapInfo').textContent = `Koordinat: ${lat}, ${lon}`;
      }, 100);
    }

    function closeModal() {
      document.getElementById('mapModal').classList.remove('show');
    }

    function showDetail(id) {
      window.location.href = 'attendance_detail.php?id=' + id;
    }

    // Close modal on outside click
    document.getElementById('mapModal').addEventListener('click', function (e) {
      if (e.target === this) {
        closeModal();
      }
    });
  </script>
</body>

</html>