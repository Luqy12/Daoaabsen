# Sistem Absensi Karyawan - BPJS Ketenagakerjaan

Sistem absensi berbasis web untuk manajemen kehadiran karyawan terintegrasi dengan BPJS Ketenagakerjaan.

## 🌐 Demo Online

**Live Website**: [https://absensitugasdaffa.infinityfreeapp.com/Absensi-web/](https://absensitugasdaffa.infinityfreeapp.com/Absensi-web/)

**GitHub Repository**: [https://github.com/Luqy12/Daoaabsen.git](https://github.com/Luqy12/Daoaabsen.git)

> **Catatan**: Browser mungkin menampilkan warning karena hosting gratis. Klik "Advanced" → "Proceed" untuk mengakses.

---

## 🚀 Tech Stack

- **Backend**: PHP Native 8.x
- **Database**: MySQL 5.7
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Server**: InfinityFree Hosting (Free Tier)
- **Icons**: SVG Custom Icons (Feather Icons inspired)
- **QR Code**: PHP QR Code Generator
- **Maps**: Leaflet.js + OpenStreetMap
- **Design**: Custom CSS with Corporate BPJS Branding

---

## ✨ Fitur Utama

### 👨‍💼 Portal Karyawan
- ✅ Login dengan NIK
- ✅ Clock In/Out dengan GPS tracking
- ✅ Scan QR Code untuk absensi
- ✅ View history kehadiran pribadi
- ✅ Download laporan absensi (CSV/Excel)
- ✅ Profile management

### 👨‍💻 Portal Administrator
- ✅ Dashboard dengan statistik real-time
- ✅ Manajemen data karyawan (CRUD)
- ✅ Monitor kehadiran semua karyawan
- ✅ Laporan lengkap dengan filter
- ✅ Generate QR Code untuk karyawan
- ✅ GPS tracking dengan peta interaktif
- ✅ Export data ke Excel/CSV
- ✅ Settings aplikasi

### 🔒 Keamanan
- ✅ CSRF Protection
- ✅ Password hashing (bcrypt)
- ✅ Session management
- ✅ Input validation & sanitization
- ✅ SQL injection prevention (Prepared Statements)

---

## 📁 Struktur Project

```
Absensi-web/
├── assets/
│   ├── css/
│   │   ├── style.css           # Main stylesheet
│   │   └── backgrounds.css     # Background patterns
│   ├── js/
│   │   └── main.js            # JavaScript utilities
│   └── images/
│       ├── logo-bpjs.png      # Logo BPJS
│       └── hero-bg-building.png
├── includes/
│   └── icons.php              # SVG Icons library
├── qrcodes/                   # Generated QR codes
├── uploads/                   # Employee photos
├── index.php                  # Landing page
├── employee_login.php         # Employee login
├── employee_portal.php        # Employee dashboard
├── checkin.php               # Clock in/out page
├── admin_login.php           # Admin login
├── dashboard.php             # Admin dashboard
├── admin.php                 # Attendance data
├── employees.php             # Employee management
├── reports.php               # Reports & analytics
├── settings.php              # App settings
├── contact.php               # Contact page
├── privacy-policy.php        # Privacy policy
├── terms-conditions.php      # Terms & conditions
├── db.php                    # Database connection
├── csrf.php                  # CSRF protection
├── install.sql               # Database schema
└── README.md                 # This file
```

---

## 🛠️ Instalasi Lokal

### Prerequisites
- XAMPP/WAMP/LAMP
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Browser modern

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/Luqy12/Daoaabsen.git
   cd Daoaabsen
   ```

2. **Setup Database**
   - Buka phpMyAdmin: `http://localhost/phpmyadmin`
   - Buat database baru: `absensitest`
   - Import file: `install.sql`

3. **Konfigurasi Database**
   
   Edit file `db.php`:
   ```php
   $host = 'localhost';
   $user = 'root';
   $pass = '';
   $db = 'absensitest';
   ```

4. **Jalankan Aplikasi**
   
   Akses: `http://localhost/Absensi-web/`

---

## 👤 Default Credentials

### Admin
- **Username**: `admin`
- **Password**: `admin123`

### Karyawan (Contoh)
- **NIK**: `EMP001`
- **Password**: `password123`

> ⚠️ **Penting**: Ubah password default setelah login pertama!

---

## 🌍 Deployment ke Hosting

### InfinityFree (Gratis)

1. **Registrasi** di [InfinityFree.com](https://infinityfree.com)
2. **Buat hosting account** dengan subdomain gratis
3. **Upload files** via File Manager atau FTP
4. **Buat database** MySQL
5. **Import** file `install.sql`
6. **Update** `db.php` dengan credentials hosting
7. **Test** website online

### Informasi Hosting Saat Ini
- **Provider**: InfinityFree
- **Domain**: absensitugasdaffa.infinityfreeapp.com
- **Storage**: Unlimited
- **Bandwidth**: Unlimited
- **SSL**: Enabled (HTTPS)

---

## 📊 Database Schema

### Tables

- **admins** - Data administrator
- **employees** - Data karyawan
- **attendances** - Data kehadiran
- **settings** - Pengaturan aplikasi
- **activity_logs** - Log aktivitas sistem
- **departments** - Data departemen

### Relationships

```
employees
  ├─ 1:N → attendances
  └─ N:1 → departments

admins
  └─ 1:N → activity_logs
```

---

## 🎨 Design System

### Color Palette (BPJS Corporate)

- **Primary Blue**: `#0066CC` - BPJS brand color
- **Secondary Green**: `#00A651` - Success, active states
- **Gray Scale**: `#F9FAFB` to `#111827` - UI elements
- **Accent**: `#F59E0B` - Warnings, highlights

### Typography

- **Font Family**: Inter, system-ui, sans-serif
- **Headings**: 700 weight
- **Body**: 400 weight
- **UI**: 500-600 weight

---

## 🔧 Konfigurasi

### GPS Settings
Default office location (dapat diubah di Settings):
- **Latitude**: -6.2088
- **Longitude**: 106.8456
- **Radius**: 100 meters

### QR Code
- **Format**: PNG
- **Size**: 300x300 px
- **Error Correction**: Medium

---

## 📱 Browser Support

✅ Chrome 90+
✅ Firefox 88+
✅ Safari 14+
✅ Edge 90+
✅ Mobile browsers (iOS Safari, Chrome Mobile)

---

## 🐛 Known Issues

- [ ] Warning "Dangerous Site" dari browser (false positive dari hosting gratis)
- [ ] QR Code scanner butuh HTTPS untuk akses camera
- [ ] GPS tracking butuh permission dari browser

---

## 📈 Future Improvements

- [ ] Mobile app (React Native)
- [ ] Fingerprint integration
- [ ] Shift management
- [ ] Leave management
- [ ] Payroll integration
- [ ] Email notifications
- [ ] Multi-language support
- [ ] Dark mode

---

## 👨‍💻 Developer

**Daffa**
- GitHub: [@Luqy12](https://github.com/Luqy12)
- Project: Tugas Kuliah - Sistem Absensi

---

## 📄 License

Educational use only. Created for academic purposes.

---

## 🙏 Acknowledgments

- BPJS Ketenagakerjaan (branding inspiration)
- Feather Icons (icon design inspiration)
- InfinityFree (free hosting)
- OpenStreetMap (GPS maps)

---

## 📞 Support

Untuk pertanyaan atau bantuan, silakan:
- Create issue di GitHub
- Contact via website: [Contact Page](https://absensitugasdaffa.infinityfreeapp.com/Absensi-web/contact.php)

---

**Last Updated**: December 2025
