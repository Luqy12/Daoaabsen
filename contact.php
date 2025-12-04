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
    <title>Hubungi Kami - <?php echo htmlspecialchars($company_name); ?></title>
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

        p {
            color: var(--gray-700);
            line-height: 1.7;
            margin-bottom: var(--space-3);
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-6);
            margin: var(--space-6) 0;
        }

        .contact-item {
            padding: var(--space-6);
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            border-left: 4px solid var(--bpjs-blue);
        }

        .contact-item h3 {
            color: var(--bpjs-blue);
            margin-bottom: var(--space-3);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .contact-item p {
            margin: 0;
            color: var(--gray-700);
        }

        .icon-large {
            width: 24px;
            height: 24px;
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
            <h1><?php echo SVGIcons::mail(); ?> Hubungi Kami</h1>

            <p>Kami siap membantu Anda dengan pertanyaan, masalah teknis, atau umpan balik terkait Sistem Absensi
                Karyawan
                <?php echo htmlspecialchars($company_name); ?>. Silakan hubungi kami melalui saluran berikut:
            </p>

            <div class="contact-grid">
                <!-- Call Center -->
                <div class="contact-item">
                    <h3>
                        <div class="icon-large"><?php echo SVGIcons::phone(); ?></div>
                        Call Center
                    </h3>
                    <p><strong>175</strong></p>
                    <p style="font-size: var(--text-sm); color: var(--gray-600);">
                        Senin - Jumat: 08:00 - 17:00 WIB<br>
                        Sabtu: 08:00 - 13:00 WIB
                    </p>
                </div>

                <!-- Email Support -->
                <div class="contact-item">
                    <h3>
                        <div class="icon-large"><?php echo SVGIcons::mail(); ?></div>
                        Email
                    </h3>
                    <p><strong>support@bpjsketenagakerjaan.go.id</strong></p>
                    <p style="font-size: var(--text-sm); color: var(--gray-600);">
                        Respon dalam 1x24 jam kerja
                    </p>
                </div>

                <!-- WhatsApp -->
                <div class="contact-item">
                    <h3>
                        <div class="icon-large"><?php echo SVGIcons::phone(); ?></div>
                        WhatsApp
                    </h3>
                    <p><strong>+62 811 175 175</strong></p>
                    <p style="font-size: var(--text-sm); color: var(--gray-600);">
                        Chat langsung dengan tim support
                    </p>
                </div>
            </div>

            <h2><?php echo SVGIcons::mapPin(); ?> Alamat Kantor</h2>
            <div class="contact-item">
                <p><strong>Kantor Pusat BPJS Ketenagakerjaan</strong></p>
                <p>
                    Jl. Gatot Subroto No.Kav. 38<br>
                    RT.1/RW.1, Kuningan Bar., Kec. Mampang Prpt.<br>
                    Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12710
                </p>
            </div>

            <h2><?php echo SVGIcons::clock(); ?> Jam Layanan</h2>
            <div class="contact-item">
                <p><strong>Senin - Jumat</strong></p>
                <p>08:00 - 17:00 WIB (Istirahat: 12:00 - 13:00)</p>
                <p style="margin-top: var(--space-3);"><strong>Sabtu</strong></p>
                <p>08:00 - 13:00 WIB</p>
                <p style="margin-top: var(--space-3);"><strong>Minggu & Hari Libur Nasional</strong></p>
                <p>Tutup</p>
            </div>

            <h2><?php echo SVGIcons::alertCircle(); ?> Dukungan Teknis</h2>
            <p>Untuk masalah teknis terkait sistem absensi, silakan hubungi:</p>
            <div class="contact-item">
                <p><strong>Email:</strong> it-support@bpjsketenagakerjaan.go.id</p>
                <p><strong>Telepon:</strong> 021-2525 1818</p>
                <p style="margin-top: var(--space-3); font-size: var(--text-sm); color: var(--gray-600);">
                    Saat menghubungi, harap siapkan informasi berikut:
                </p>
                <ul style="margin-left: var(--space-4); font-size: var(--text-sm); color: var(--gray-600);">
                    <li>NIK/NIP Anda</li>
                    <li>Deskripsi masalah yang dihadapi</li>
                    <li>Screenshot error (jika ada)</li>
                    <li>Browser dan perangkat yang digunakan</li>
                </ul>
            </div>

            <h2><?php echo SVGIcons::info(); ?> Media Sosial</h2>
            <p>Ikuti kami untuk mendapatkan informasi terkini:</p>
            <div class="contact-grid">
                <div class="contact-item">
                    <p><strong>Instagram</strong></p>
                    <p>@bpjsketenagakerjaan</p>
                </div>
                <div class="contact-item">
                    <p><strong>Twitter</strong></p>
                    <p>@BPJS_TK</p>
                </div>
                <div class="contact-item">
                    <p><strong>Facebook</strong></p>
                    <p>BPJS Ketenagakerjaan</p>
                </div>
            </div>

            <div
                style="margin-top: var(--space-8); padding: var(--space-6); background: var(--primary-50); border-radius: var(--radius-lg); border-left: 4px solid var(--bpjs-blue);">
                <p style="margin: 0; font-weight: 600;">
                    <?php echo SVGIcons::checkCircle(); ?> Catatan Penting:
                </p>
                <p style="margin: var(--space-2) 0 0; font-size: var(--text-sm);">
                    Untuk keamanan data Anda, jangan pernah membagikan password atau kode OTP kepada siapapun, termasuk
                    petugas BPJS Ketenagakerjaan. Kami tidak akan pernah meminta informasi sensitif Anda melalui telepon
                    atau
                    email.
                </p>
            </div>

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