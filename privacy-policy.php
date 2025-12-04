<?php
session_start();
require_once 'includes/icons.php';
require_once 'db.php';

$mysqli = db_connect();
$settings_query = $mysqli->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $settings_query->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$mysqli->close();

$company_name = $settings['company_name'] ?? 'BPJS Ketenagakerjaan';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - <?php echo htmlspecialchars($company_name); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: var(--gray-50);
        }

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

        .content-wrapper {
            max-width: 900px;
            margin: var(--space-8) auto;
            padding: 0 var(--space-4);
        }

        .card {
            background: white;
            padding: var(--space-8);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        h1 {
            color: var(--bpjs-blue);
            margin-bottom: var(--space-6);
            font-size: var(--text-3xl);
        }

        h2 {
            color: var(--bpjs-blue);
            margin-top: var(--space-6);
            margin-bottom: var(--space-3);
            font-size: var(--text-xl);
        }

        p,
        li {
            color: var(--gray-700);
            line-height: 1.7;
            margin-bottom: var(--space-3);
        }

        ul {
            margin-left: var(--space-6);
            margin-bottom: var(--space-4);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div>
                <img src="assets/images/logo-bpjs.png" alt="<?php echo htmlspecialchars($company_name); ?>"
                    class="logo">
            </div>
            <a href="index.php" class="btn btn-outline"><?php echo SVGIcons::arrowLeft(); ?> Kembali</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="card">
            <h1><?php echo SVGIcons::shield(); ?> Kebijakan Privasi</h1>

            <p><strong>Terakhir diperbarui:</strong> <?php echo date('d F Y'); ?></p>

            <p>Sistem Absensi Karyawan <?php echo htmlspecialchars($company_name); ?> ("kami", "kita") berkomitmen untuk
                melindungi privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan
                melindungi informasi pribadi Anda saat menggunakan sistem absensi kami.</p>

            <h2>1. Informasi yang Kami Kumpulkan</h2>
            <p>Kami mengumpulkan informasi berikut:</p>
            <ul>
                <li><strong>Data Identitas:</strong> Nama lengkap, NIK/NIP, email, nomor telepon</li>
                <li><strong>Data Kehadiran:</strong> Waktu check-in/check-out, lokasi GPS, foto selfie</li>
                <li><strong>Data Pekerjaan:</strong> Departemen, jabatan, status kepegawaian</li>
                <li><strong>Data Izin:</strong> Pengajuan izin, sakit, cuti dan dokumen pendukung</li>
            </ul>

            <h2>2. Penggunaan Informasi</h2>
            <p>Informasi yang kami kumpulkan digunakan untuk:</p>
            <ul>
                <li>Mencatat dan memantau kehadiran karyawan</li>
                <li>Menghasilkan laporan absensi dan statistik</li>
                <li>Memproses pengajuan izin, sakit, dan cuti</li>
                <li>Menghitung benefit BPJS Ketenagakerjaan</li>
                <li>Memverifikasi identitas pengguna</li>
                <li>Meningkatkan kualitas layanan sistem</li>
            </ul>

            <h2>3. Keamanan Data</h2>
            <p>Kami menerapkan langkah-langkah keamanan teknis dan organisasi yang sesuai untuk melindungi data pribadi
                Anda,
                termasuk:</p>
            <ul>
                <li>Enkripsi data saat transmisi dan penyimpanan</li>
                <li>Kontrol akses berbasis role (admin, karyawan)</li>
                <li>Pencatatan audit trail untuk setiap aktivitas</li>
                <li>Backup data secara berkala</li>
                <li>Proteksi CSRF untuk keamanan form</li>
            </ul>

            <h2>4. Berbagi Informasi</h2>
            <p>Kami tidak akan membagikan informasi pribadi Anda kepada pihak ketiga kecuali:</p>
            <ul>
                <li>Diwajibkan oleh hukum atau regulasi yang berlaku</li>
                <li>Dengan persetujuan eksplisit dari Anda</li>
                <li>Untuk keperluan integrasi dengan sistem BPJS Ketenagakerjaan resmi</li>
                <li>Kepada penyedia layanan yang membantu operasional sistem dengan perjanjian kerahasiaan</li>
            </ul>

            <h2>5. Hak Anda</h2>
            <p>Anda memiliki hak untuk:</p>
            <ul>
                <li>Mengakses dan melihat data pribadi Anda</li>
                <li>Meminta koreksi data yang tidak akurat</li>
                <li>Mengunduh riwayat absensi Anda</li>
                <li>Mengajukan keluhan terkait penggunaan data</li>
            </ul>

            <h2>6. Penyimpanan Data</h2>
            <p>Data absensi dan informasi pribadi akan disimpan selama masa kepegawaian aktif ditambah waktu yang
                diperlukan untuk kepentingan hukum, perpajakan, dan arsip perusahaan sesuai dengan peraturan yang
                berlaku.</p>

            <h2>7. Perubahan Kebijakan</h2>
            <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan akan diumumkan melalui sistem
                dan
                berlaku setelah tanggal pembaruan yang tertera di atas.</p>

            <h2>8. Hubungi Kami</h2>
            <p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi:</p>
            <p>
                <strong>Email:</strong> privacy@bpjsketenagakerjaan.go.id<br>
                <strong>Telepon:</strong> 175<br>
                <strong>Alamat:</strong> Jl. Gatot Subroto No.Kav. 38, Jakarta Selatan
            </p>

            <div
                style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--gray-200);">
                <p style="text-align: center; color: var(--gray-500); font-size: var(--text-sm);">
                    © <?php echo date('Y'); ?> <?php echo htmlspecialchars($company_name); ?>. Hak Cipta Dilindungi.
                </p>
            </div>
        </div>
    </div>
</body>

</html>