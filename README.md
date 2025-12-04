# Absensi Web - Modern Employee Attendance System

Sistem Absensi Karyawan Modern menggunakan **PHP Native + MySQL** dengan fitur QR Code, GPS Location, dan Dashboard Statistik.

## ✨ Fitur Utama

### 🎯 Untuk Karyawan
- **QR Code Scanner** - Scan QR code untuk absen cepat menggunakan kamera HP
- **Input Manual** - Alternatif input manual dengan kode karyawan
- **GPS Location** - Otomatis merekam lokasi saat absen
- **Multi-Type Attendance** - Check-in, Check-out, Izin, Sakit, Cuti
- **Photo Capture** - Upload foto saat absen (opsional)
- **Portal Karyawan** - Dashboard pribadi untuk melihat riwayat absensi
- **Quick Actions** - Tombol cepat untuk checkin/checkout

### 👨‍💼 Untuk Admin
- **Dashboard Statistik** - Grafik kehadiran, total hadir hari ini, terlambat, dll
- **Data Absensi Lengkap** - Filter berdasarkan tanggal, jenis, status, dan pencarian
- **Peta Lokasi** - Tampilkan lokasi absensi di peta interaktif (OpenStreetMap)
- **Kelola Karyawan** - CRUD karyawan dengan auto-generate QR code
- **Export Excel** - Export data ke Excel/CSV dengan filter
- **Departemen Management** - Atur departemen dengan jam kerja berbeda
- **Activity Logs** - Log semua aktivitas admin dan karyawan
- **Pagination** - Navigasi data yang efisien

### 🎨 UI/UX Modern
- **Design System Lengkap** - CSS Variables, gradients, animations
- **Responsive Design** - Mobile-first, works on all devices
- **Glassmorphism** - Modern UI dengan backdrop blur
- **Smooth Animations** - Transitions dan hover effects
- **Google Fonts** - Inter & Roboto Mono
- **Dark Gradient Background** - Eye-catching gradient backgrounds
- **Beautiful Cards & Badges** - Modern component design

### 🔐 Keamanan
- **CSRF Protection** - Token di semua form
- **Password Hashing** - Bcrypt untuk semua password
- **Prepared Statements** - SQL injection prevention
- **Input Validation** - Validasi di frontend dan backend
- **Activity Logging** - Track semua aktivitas penting

## 🚀 Teknologi

- **Backend**: PHP Native (7.4+)
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **QR Code**: HTML5-QRCode library
- **Maps**: Leaflet.js + OpenStreetMap
- **Charts**: Chart.js
- **Icons**: Emoji (no external icon fonts needed!)

## 📦 Instalasi

### 1. Persyaratan
- PHP >= 7.4
- MySQL/MariaDB
- Web server (Apache/Nginx) atau XAMPP/Laragon
- Extension PHP: `mysqli`, `gd` (untuk manipulasi gambar)

### 2. Setup

```bash
# 1. Clone/ekstrak ke folder web server
# Windows (XAMPP): C:\xampp\htdocs\absensi-web
# Linux: /var/www/html/absensi-web

# 2. Buat database
# Buka phpMyAdmin atau MySQL client

# 3. Import schema
mysql -u root -p < install.sql

# 4. Edit konfigurasi database (jika perlu)
# File: db.php
# Sudah diset ke database 'absensitest'

# 5. Set permission untuk upload folders
chmod 755 uploads/
chmod 755 qrcodes/

# 6. Akses aplikasi
# http://localhost/absensi-web/
```

### 3. Login Default

**Admin:**
- Username: `admin`
- Password: `admin123`

**Karyawan:**  
Karyawan harus login menggunakan `employee_code` + password yang di-set admin di halaman Kelola Karyawan.

## 📁 Struktur Folder

```
absensi-web/
├── assets/
│   ├── css/
│   │   └── style.css          # Design system lengkap
│   └── js/
│       └── main.js             # (kosong, logic inline di halaman)
├── uploads/                    # Foto absensi
├── qrcodes/                    # QR Code karyawan
├── index.php                   # Landing + QR Scanner
├── admin_login.php            # Login admin
├── employee_login.php         # Login karyawan
├── dashboard.php              # Dashboard admin
├── admin.php                  # Data absensi + filter
├── employee_portal.php        # Portal karyawan
├── employees.php              # Kelola karyawan + QR
├── checkin.php                # API handle absensi
├── logout.php                 # Logout admin
├── employee_logout.php        # Logout karyawan
├── db.php                     # Database connection
├── csrf.php                   # CSRF protection
├── install.sql                # Database schema
└── README.md                  # Dokumentasi
```

## 🎯 Cara Penggunaan

### Alur Admin
1. Login di `/admin_login.php`
2. Dashboard akan menampilkan statistik hari ini
3. Tambah karyawan di menu "Kelola Karyawan"
4. QR Code akan di-generate otomatis
5. Print/download QR Code untuk karyawan
6. Monitor absensi di "Data Absensi"
7. Export data jika perlu

### Alur Karyawan (via QR)
1. Buka halaman utama `/index.php`
2. Klik "Mulai Scanner"
3. Arahkan kamera ke QR Code
4. Absensi otomatis tercatat!

### Alur Karyawan (via Portal)
1. Login di `/employee_login.php`
2. Gunakan Quick Actions atau Form Manual
3. Lihat riwayat absensi pribadi

## 🔧 Konfigurasi

### Setting Lokasi Kantor
Edit di database tabel `settings`:
```sql
UPDATE settings SET setting_value = '-6.2088' WHERE setting_key = 'office_lat';
UPDATE settings SET setting_value = '106.8456' WHERE setting_key = 'office_lon';
```

### Jam Kerja per Departemen
Atur di tabel `departments`:
- `work_start`: Jam masuk (default 08:00:00)
- `work_end`: Jam pulang (default 17:00:00)
- `late_tolerance`: Toleransi terlambat dalam menit (default 15)

## 📊 Fitur Database

### Status Kehadiran
- `on_time` - Tepat waktu
- `late` - Terlambat (< 30 menit)
- `very_late` - Sangat terlambat (>= 30 menit)
- `permit` - Izin/Sakit/Cuti

### Perhitungan Otomatis
-  Jarak dari kantor (Haversine formula)
- Status keterlambatan berdasarkan jam kerja departemen
- Log aktivitas semua user

## 🎨 Customisasi

### Warna Tema
Edit CSS variables di `assets/css/style.css`:
```css
:root {
  --primary-500: #3b82f6;  /* Warna utama */
  --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  /* ... dll */
}
```

### Logo Perusahaan
Update di tabel `settings`:
```sql
UPDATE settings SET setting_value = 'PT ABC Indonesia' WHERE setting_key = 'company_name';
```

## 📝 TODO / Future Features

- [ ] Export to PDF
- [ ] Email notifications untuk terlambat
- [ ] Shift management
- [ ] Overtime tracking
- [ ] Leave request approval workflow
- [ ] Mobile app (PWA)
- [ ] Fingerprint integration
- [ ] Multi-language support

## 🤝 Kontribusi

Silakan fork, modifikasi, dan gunakan sesuai kebutuhan. Jangan lupa beri credit!

## 📄 License

MIT License - Free to use for personal and commercial projects.

## 🙏 Credits

- QR Scanner: [HTML5-QRCode](https://github.com/mebjas/html5-qrcode)
- Maps: [Leaflet.js](https://leafletjs.com/) + [OpenStreetMap](https://www.openstreetmap.org/)
- Charts: [Chart.js](https://www.chartjs.org/)
- Design Inspiration: Modern web design trends 2024

---

**Dibuat dengan ❤️ menggunakan PHP Native**

Untuk pertanyaan dan support, silakan buka issue di repository ini.
