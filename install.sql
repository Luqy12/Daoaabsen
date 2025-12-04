-- Database Sistem Absensi Karyawan Modern
-- Buat database
CREATE DATABASE IF NOT EXISTS absensitest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE absensitest;

-- Tabel Departemen
CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  work_start TIME DEFAULT '08:00:00',
  work_end TIME DEFAULT '17:00:00',
  late_tolerance INT DEFAULT 15 COMMENT 'Toleransi terlambat dalam menit',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(255) DEFAULT NULL,
  email VARCHAR(255),
  role ENUM('super_admin','admin') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- Tabel Karyawan (dengan fitur lengkap)
CREATE TABLE IF NOT EXISTS employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  phone VARCHAR(50),
  password VARCHAR(255) DEFAULT NULL,
  department_id INT DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL COMMENT 'Path foto profil',
  qr_code VARCHAR(255) DEFAULT NULL COMMENT 'Path QR code',
  address TEXT,
  status ENUM('active','inactive','suspended') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_department (department_id)
) ENGINE=InnoDB;

-- Tabel Absensi (dengan status dan durasi)
CREATE TABLE IF NOT EXISTS attendances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  type ENUM('checkin','checkout','izin','sakit','cuti') DEFAULT 'checkin',
  status ENUM('on_time','late','very_late','permit') DEFAULT 'on_time',
  lat DECIMAL(10,7) DEFAULT NULL,
  lon DECIMAL(10,7) DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL COMMENT 'Alamat dari reverse geocoding',
  note VARCHAR(500) DEFAULT NULL,
  photo_path VARCHAR(255) DEFAULT NULL,
  work_duration INT DEFAULT NULL COMMENT 'Durasi kerja dalam menit',
  distance_from_office DECIMAL(8,2) DEFAULT NULL COMMENT 'Jarak dari kantor dalam meter',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_addr VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  INDEX idx_employee_date (employee_id, created_at),
  INDEX idx_type (type),
  INDEX idx_status (status)
) ENGINE=InnoDB;

-- Tabel Pengaturan Aplikasi
CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  setting_type ENUM('string','number','boolean','json') DEFAULT 'string',
  description VARCHAR(255),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Log Aktivitas
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_type ENUM('admin','employee') NOT NULL,
  user_id INT NOT NULL,
  action VARCHAR(100) NOT NULL COMMENT 'login, logout, add_employee, edit_employee, etc',
  description TEXT,
  ip_addr VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_type, user_id),
  INDEX idx_action (action)
) ENGINE=InnoDB;

-- Insert data awal departemen
INSERT INTO departments (name, description, work_start, work_end, late_tolerance) VALUES
('IT & Technology', 'Departemen Teknologi Informasi', '08:00:00', '17:00:00', 15),
('Human Resources', 'Departemen Sumber Daya Manusia', '08:00:00', '17:00:00', 10),
('Finance', 'Departemen Keuangan', '08:00:00', '17:00:00', 5),
('Marketing', 'Departemen Pemasaran', '09:00:00', '18:00:00', 15),
('Operations', 'Departemen Operasional', '07:00:00', '16:00:00', 10);

-- Insert default admin (username: admin, password: admin123)
-- Password hash untuk 'admin123'
INSERT INTO admins (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@absensi.com', 'super_admin');

-- Insert contoh karyawan
INSERT INTO employees (employee_code, name, email, phone, department_id, status) VALUES
('EMP001', 'Budi Santoso', 'budi.santoso@company.com', '081234567890', 1, 'active'),
('EMP002', 'Siti Nurhaliza', 'siti.nurhaliza@company.com', '081298765432', 2, 'active'),
('EMP003', 'Ahmad Rifai', 'ahmad.rifai@company.com', '081356789012', 3, 'active'),
('EMP004', 'Dewi Lestari', 'dewi.lestari@company.com', '081445678901', 4, 'active'),
('EMP005', 'Eko Prasetyo', 'eko.prasetyo@company.com', '081523456789', 5, 'active');

-- Insert pengaturan default
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('company_name', 'PT Absensi Modern Indonesia', 'string', 'Nama Perusahaan'),
('company_address', 'Jl. Sudirman No. 123, Jakarta', 'string', 'Alamat Perusahaan'),
('office_lat', '-6.2088', 'number', 'Latitude Kantor'),
('office_lon', '106.8456', 'number', 'Longitude Kantor'),
('max_distance', '500', 'number', 'Maksimal jarak absen dari kantor (meter)'),
('timezone', 'Asia/Jakarta', 'string', 'Timezone aplikasi'),
('enable_gps', 'true', 'boolean', 'Aktifkan validasi GPS'),
('enable_photo', 'true', 'boolean', 'Wajibkan foto saat absen'),
('theme', 'light', 'string', 'Tema aplikasi (light/dark)');
