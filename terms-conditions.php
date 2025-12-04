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
    <title>Syarat & Ketentuan - <?php echo htmlspecialchars($company_name); ?></title>
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

        ul,
        ol {
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
            <h1><?php echo SVGIcons::clipboard(); ?> Syarat & Ketentuan</h1>

            <p><strong>Terakhir diperbarui:</strong> <?php echo date('d F Y'); ?></p>

            <p>Selamat datang di Sistem Absensi Karyawan <?php echo htmlspecialchars($company_name); ?>. Dengan
                mengakses
                dan menggunakan sistem ini, Anda setuju untuk terikat dengan Syarat dan Ketentuan berikut.</p>

            <h2>1. Penggunaan Sistem</h2>
            <h3>1.1 Akses Pengguna</h3>
            <ul>
                <li>Setiap pengguna akan diberikan akun dengan kredensial unik (username dan password)</li>
                <li>Anda bertanggung jawab untuk menjaga kerahasiaan kredensial akun Anda</li>
                <li>Dilarang membagikan kredensial akun kepada pihak lain</li>
                <li>Segera laporkan jika terjadi penggunaan akun yang tidak sah</li>
            </ul>

            <h3>1.2 Waktu dan Lokasi Absensi</h3>
            <ul>
                <li>Absensi harus dilakukan pada waktu yang telah ditentukan perusahaan</li>
                <li>Lokasi GPS akan direkam untuk memverifikasi keberadaan Anda</li>
                <li>Foto selfie wajib diambil saat melakukan check-in/check-out</li>
                <li>Sistem akan mendeteksi keterlambatan sesuai dengan aturan perusahaan</li>
            </ul>

            <h2>2. Kewajiban Pengguna</h2>
            <p>Sebagai pengguna sistem, Anda wajib:</p>
            <ul>
                <li>Melakukan absensi secara jujur dan akurat</li>
                <li>Tidak memanipulasi data absensi atau lokasi GPS</li>
                <li>Menggunakan sistem sesuai dengan tujuan yang dimaksudkan</li>
                <li>Melaporkan masalah teknis atau ketidakakuratan data</li>
                <li>Mematuhi semua kebijakan perusahaan terkait kehadiran</li>
            </ul>

            <h2>3. Larangan</h2>
            <p>Pengguna dilarang:</p>
            <ul>
                <li>Melakukan absensi untuk orang lain (titip absen)</li>
                <li>Menggunakan aplikasi atau metode untuk memalsukan lokasi GPS</li>
                <li>Mengakses akun pengguna lain tanpa izin</li>
                <li>Mencoba mengubah, merusak, atau mengganggu sistem</li>
                <li>Menggunakan sistem untuk tujuan ilegal atau tidak etis</li>
                <li>Melakukan reverse engineering atau dekompilasi sistem</li>
            </ul>

            <h2>4. Pengajuan Izin, Sakit, dan Cuti</h2>
            <ul>
                <li>Pengajuan harus dilakukan melalui sistem dengan dokumen pendukung yang sah</li>
                <li>Persetujuan izin tunduk pada kebijakan dan persetujuan manajemen</li>
                <li>Dokumen pendukung seperti surat sakit harus asli dan dapat diverifikasi</li>
                <li>Pengajuan yang terbukti palsu akan dikenakan sanksi sesuai aturan perusahaan</li>
            </ul>

            <h2>5. Data dan Privasi</h2>
            <ul>
                <li>Data absensi Anda adalah rahasia dan hanya dapat diakses oleh pihak yang berwenang</li>
                <li>Perusahaan berhak menggunakan data untuk keperluan administrasi, payroll, dan pelaporan</li>
                <li>Data dapat dibagikan dengan BPJS Ketenagakerjaan untuk keperluan program jaminan sosial</li>
                <li>Lihat Kebijakan Privasi untuk informasi lebih detail</li>
            </ul>

            <h2>6. Sanksi Pelanggaran</h2>
            <p>Pelanggaran terhadap Syarat dan Ketentuan ini dapat mengakibatkan:</p>
            <ol>
                <li>Peringatan tertulis</li>
                <li>Suspensi sementara akses sistem</li>
                <li>Sanksi administratif sesuai aturan perusahaan</li>
                <li>Pemutusan hubungan kerja untuk pelanggaran berat</li>
                <li>Tindakan hukum jika terbukti melakukan tindakan kriminal</li>
            </ol>

            <h2>7. Hak Perusahaan</h2>
            <p>Perusahaan berhak:</p>
            <ul>
                <li>Mengubah Syarat dan Ketentuan kapan saja dengan pemberitahuan sebelumnya</li>
                <li>Menonaktifkan akun yang melanggar ketentuan</li>
                <li>Melakukan audit terhadap data absensi</li>
                <li>Menghentikan atau memodifikasi fitur sistem</li>
                <li>Menggunakan data agregat untuk analisis dan peningkatan sistem</li>
            </ul>

            <h2>8. Dukungan Teknis</h2>
            <ul>
                <li>Dukungan teknis tersedia selama jam kerja</li>
                <li>Laporkan masalah melalui email atau telepon support</li>
                <li>Kami akan berusaha menyelesaikan masalah dalam waktu 1x24 jam</li>
            </ul>

            <h2>9. Keadaan Kahar (Force Majeure)</h2>
            <p>Perusahaan tidak bertanggung jawab atas kegagalan sistem yang disebabkan oleh kejadian di luar kendali,
                termasuk bencana alam, gangguan listrik, gangguan internet, atau serangan cyber.</p>

            <h2>10. Hukum yang Berlaku</h2>
            <p>Syarat dan Ketentuan ini diatur oleh dan ditafsirkan sesuai dengan hukum Negara Republik Indonesia.
                Setiap
                sengketa akan diselesaikan melalui jalur hukum yang berlaku di Indonesia.</p>

            <h2>11. Hubungi Kami</h2>
            <p>Jika Anda memiliki pertanyaan tentang Syarat & Ketentuan ini, silakan hubungi:</p>
            <p>
                <strong>Email:</strong> legal@bpjsketenagakerjaan.go.id<br>
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