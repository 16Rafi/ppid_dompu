<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php';
require_once '../includes/email_config.php';
require_once '../includes/file_upload.php';
require_once '../includes/email_service.php';
require_once '../includes/permohonan_service.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Set headers
header('Content-Type: text/html; charset=UTF-8');

// Initialize services
$fileHandler = new FileUploadHandler();
$emailService = new EmailService();

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Process form submission with database and email
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $uploadedFile = null;
    $uploadedFilePath = '';
    
    // Validasi required fields
    $required_fields = ['nama_lengkap', 'alamat', 'email', 'pekerjaan', 'jenis_identitas', 'nomor_identitas', 'tujuan_perangkat', 'tujuan_informasi', 'cara_mendapatkan', 'cara_pengambilan'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Field " . str_replace('_', ' ', $field) . " wajib diisi";
        }
    }

    if (empty($_FILES['scan_identitas']['name'])) {
        $errors[] = "Scan identitas wajib diupload";
    }
    
    // Validasi email
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Handle file upload if provided
    if (!empty($_FILES['scan_identitas']['name'])) {
        $uploadResult = $fileHandler->handleUpload('scan_identitas', 'file');
        if (!$uploadResult['success']) {
            $errors[] = "Upload file gagal: " . $uploadResult['error'];
        } else {
            $uploadedFile = $uploadResult['original_name'];
            $uploadedFilePath = $uploadResult['filepath'];
            $scanPath = 'uploads/' . $uploadResult['filename'];
        }
    }
    
    if (empty($errors)) {
        // Prepare data for database
        $permohonanData = [
            'nama_pemohon' => $_POST['nama_lengkap'],
            'email' => $_POST['email'],
            'no_hp' => $_POST['no_hp'] ?? '',
            'alamat' => $_POST['alamat'],
            'pekerjaan' => $_POST['pekerjaan'],
            'jenis_identitas' => $_POST['jenis_identitas'],
            'nomor_identitas' => $_POST['nomor_identitas'],
            'scan_identitas' => $scanPath ?? '',
            'tujuan_perangkat' => $_POST['tujuan_perangkat'],
            'informasi_diminta' => $_POST['tujuan_perangkat'] . ' - ' . $_POST['cara_mendapatkan'],
            'tujuan_penggunaan' => $_POST['tujuan_informasi'],
            'cara_mendapatkan' => $_POST['cara_mendapatkan'],
            'cara_pengambilan' => $_POST['cara_pengambilan'],
            'cara_memperoleh' => $_POST['cara_pengambilan']
        ];
        
        // Validate data
        $validationErrors = validatePermohonanData($permohonanData);
        if (!empty($validationErrors)) {
            $errors = array_merge($errors, $validationErrors);
        }
        
        if (empty($errors)) {
            // Sanitize data
            $sanitizedData = sanitizePermohonanData($permohonanData);
            
            // Save to database
            $saveResult = savePermohonanInformasi($sanitizedData);
            
            if ($saveResult['success']) {
                $registrationNumber = $saveResult['nomor_registrasi'];
                $recipientEmail = EmailConfig::getRecipientEmail();
                $subject = 'Permohonan Informasi Publik - ' . htmlspecialchars($_POST['nama_lengkap']) . ' - ' . $registrationNumber;
                
                // Create HTML email template
                $emailTemplate = '
                <html>
                <head>
                    <title>Permohonan Informasi Publik</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #093A5A; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background: #f9f9f9; }
                        .section { margin-bottom: 20px; }
                        .label { font-weight: bold; color: #093A5A; }
                        .footer { background: #7392A8; color: white; padding: 15px; text-align: center; font-size: 12px; }
                        table { width: 100%; border-collapse: collapse; }
                        td { padding: 8px; border-bottom: 1px solid #ddd; }
                        .label-td { font-weight: bold; background: #f0f0f0; width: 30%; }
                        .success-box { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2>🏛️ PPID Kabupaten Dompu</h2>
                            <h3>Permohonan Informasi Publik</h3>
                        </div>
                        
                        <div class="content">
                            <div class="section">
                                <p><strong>Tanggal Permohonan:</strong> ' . date('d/m/Y H:i') . '</p>
                                <p><strong>No. Registrasi:</strong> ' . $registrationNumber . '</p>
                            </div>
                            
                            <div class="section">
                                <h4>Data Pemohon</h4>
                                <table>
                                    <tr><td class="label-td">Nama Lengkap</td><td>' . htmlspecialchars($_POST['nama_lengkap']) . '</td></tr>
                                    <tr><td class="label-td">Email</td><td>' . htmlspecialchars($_POST['email']) . '</td></tr>
                                    <tr><td class="label-td">No. HP</td><td>' . htmlspecialchars($_POST['no_hp'] ?? '-') . '</td></tr>
                                    <tr><td class="label-td">Alamat</td><td>' . htmlspecialchars($_POST['alamat']) . '</td></tr>
                                    <tr><td class="label-td">Pekerjaan</td><td>' . htmlspecialchars($_POST['pekerjaan']) . '</td></tr>
                                    <tr><td class="label-td">Jenis Identitas</td><td>' . htmlspecialchars($_POST['jenis_identitas']) . '</td></tr>
                                    <tr><td class="label-td">Nomor Identitas</td><td>' . htmlspecialchars($_POST['nomor_identitas']) . '</td></tr>
                                </table>
                            </div>
                            
                            <div class="section">
                                <h4>Detail Permohonan</h4>
                                <table>
                                    <tr><td class="label-td">Tujuan Perangkat</td><td>' . htmlspecialchars($_POST['tujuan_perangkat']) . '</td></tr>
                                    <tr><td class="label-td">Tujuan Informasi</td><td>' . htmlspecialchars($_POST['tujuan_informasi']) . '</td></tr>
                                    <tr><td class="label-td">Cara Mendapatkan</td><td>' . htmlspecialchars($_POST['cara_mendapatkan']) . '</td></tr>
                                    <tr><td class="label-td">Cara Pengambilan</td><td>' . htmlspecialchars($_POST['cara_pengambilan']) . '</td></tr>
                                </table>
                            </div>';
                            
                            // Add file info if uploaded
                            if ($uploadedFilePath) {
                                $emailTemplate .= '
                                <div class="section">
                                    <h4>Dokumen Lampiran</h4>
                                    <p><strong>Nama File:</strong> ' . htmlspecialchars($uploadedFile) . '</p>
                                    <p><strong>Ukuran File:</strong> ' . number_format(filesize($uploadedFilePath) / 1024, 2) . ' KB</p>
                                </div>';
                            }
                            
                            $emailTemplate .= '
                            <div class="success-box">
                                <strong>✅ Permohonan Diterima</strong><br>
                                Permohonan informasi Anda telah diterima dan akan diproses sesuai dengan peraturan yang berlaku.
                            </div>
                        </div>
                        
                        <div class="footer">
                            <p><strong>PPID Kabupaten Dompu</strong><br>
                            Jl. Lombok No. 1, Kota Dompu, NTB<br>
                            Email: ppid@dompukab.go.id</p>
                        </div>
                    </div>
                </body>
                </html>';
                
                // Prepare attachments
                $attachments = [];
                if (!empty($uploadedFilePath)) {
                    $attachments[] = [
                        'path' => $uploadedFilePath,
                        'name' => $uploadedFile
                    ];
                }
                
                // Prepare reply-to
                $replyTo = [
                    'email' => $_POST['email'],
                    'name' => $_POST['nama_lengkap']
                ];
                
                // Send email using EmailService
                $sendResult = $emailService->sendEmail(
                    $recipientEmail,
                    $subject,
                    $emailTemplate,
                    strip_tags(str_replace(['<br>', '</p>', '</tr>'], ["\n", "\n\n", "\n"], $emailTemplate)),
                    $attachments,
                    $replyTo
                );
                
                // Handle result
                if ($sendResult['success']) {
                    // Email sent successfully to SMTP server
                    $notification = '<div class="alert alert-success">
                        <h3>✅ Permohonan Berhasil Dikirim!</h3>
                        <p>Terima kasih telah mengajukan permohonan informasi. Permohonan Anda telah kami terima dengan nomor registrasi:</p>
                        <p><strong>' . $registrationNumber . '</strong></p>
                        <p>Kami akan memproses permohonan Anda dalam waktu maksimal 10 hari kerja sesuai dengan UU No. 14 Tahun 2008.</p>
                        <p>Konfirmasi telah dikirim ke email: ' . htmlspecialchars($_POST['email']) . '</p>
                        <p><small>ID Transaksi: ' . ($sendResult['technical_details']['message_id'] ?? 'N/A') . '</small></p>
                    </div>';
                    
                } else {
                    // Email failed to send but data is saved
                    $notification = '<div class="alert alert-success">
                        <h3>✅ Permohonan Berhasil Disimpan!</h3>
                        <p>Permohonan Anda telah disimpan dengan nomor registrasi: <strong>' . $registrationNumber . '</strong></p>
                        <p>Data Anda telah tersimpan dalam database kami dan akan diproses.</p>
                        <p><small>Notifikasi email gagal dikirim, namun data Anda aman tersimpan.</small></p>
                    </div>';
                }
                
                // If AJAX request, return only the notification
                if ($isAjax) {
                    echo $notification;
                    exit;
                }
                
            } else {
                $errors[] = "Gagal menyimpan permohonan: " . $saveResult['error'];
            }
        }
    }
    
    if (!empty($errors)) {
        $errorNotification = '<div class="alert alert-error">
            <h3>❌ Proses Gagal</h3>
            <ul>';
        foreach ($errors as $error) {
            $errorNotification .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $errorNotification .= '</ul></div>';
        
        // If AJAX request, return only error notification
        if ($isAjax) {
            echo $errorNotification;
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Informasi - PPID Kabupaten Dompu</title>
    <meta name="description" content="Formulir permohonan informasi publik PPID Kabupaten Dompu">
    <link rel="stylesheet" href="/ppid_dompu/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
   <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h2>PPID</h2>
                </div>
                <nav class="nav-menu">
                    <ul class="nav-list">
                        <?php
                        global $conn;
                        $query = "SELECT * FROM menus WHERE parent_id = 0 AND is_active = 1 ORDER BY order_index";
                        $result = $conn->query($query);
                        
                        while ($menu = $result->fetch_assoc()) {
                            $has_children = false;
                            $children_query = "SELECT * FROM menus WHERE parent_id = ? AND is_active = 1 ORDER BY order_index";
                            $children_stmt = $conn->prepare($children_query);
                            $children_stmt->bind_param("i", $menu['id']);
                            $children_stmt->execute();
                            $children_result = $children_stmt->get_result();
                            
                            if ($children_result->num_rows > 0) {
                                $has_children = true;
                            }
                            
                            echo '<li class="nav-item">';
                            if ($has_children) {
                                echo '<a href="' . buildUrl($menu['url']) . '" class="nav-link dropdown-toggle">' . htmlspecialchars($menu['name']) . '</a>';
                                echo '<ul class="dropdown-menu">';
                                while ($child = $children_result->fetch_assoc()) {
                                    echo '<li><a href="' . buildUrl($child['url']) . '" class="dropdown-link">' . htmlspecialchars($child['name']) . '</a></li>';
                                }
                                echo '</ul>';
                            } else {
                                echo '<a href="' . buildUrl($menu['url']) . '" class="nav-link">' . htmlspecialchars($menu['name']) . '</a>';
                            }
                            echo '</li>';
                        }
                        ?>
                    </ul>
                </nav>
                <div class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="permohonan-page">
        <div class="page-header">
            <h1>Permohonan Informasi Publik</h1>
            <p>Isi formulir berikut untuk mengajukan permohonan informasi publik kepada PPID Kabupaten Dompu sesuai dengan Undang-Undang No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik.</p>
        </div>

        <div class="form-container permohonan-container">
            <form method="POST" enctype="multipart/form-data" id="permohonanForm">
                <!-- Identitas Pemohon -->
                <div class="form-section">
                    <h2 class="section-title">Identitas Pemohon</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="no_hp">Nomor HP</label>
                            <input type="tel" id="no_hp" name="no_hp" placeholder="Opsional">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap <span class="required">*</span></label>
                        <textarea id="alamat" name="alamat" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pekerjaan">Pekerjaan <span class="required">*</span></label>
                            <select id="pekerjaan" name="pekerjaan" required>
                                <option value="">-- Pilih Pekerjaan --</option>
                                <option value="PNS">Pegawai Negeri Sipil</option>
                                <option value="TNI/Polri">TNI/Polri</option>
                                <option value="Swasta">Swasta</option>
                                <option value="Wiraswasta">Wiraswasta</option>
                                <option value="Pelajar/Mahasiswa">Pelajar/Mahasiswa</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="jenis_identitas">Jenis Identitas <span class="required">*</span></label>
                            <select id="jenis_identitas" name="jenis_identitas" required>
                                <option value="">-- Pilih Jenis Identitas --</option>
                                <option value="KTP">KTP</option>
                                <option value="SIM">SIM</option>
                                <option value="Paspor">Paspor</option>
                                <option value="Kartu Pelajar">Kartu Pelajar</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nomor_identitas">Nomor Identitas <span class="required">*</span></label>
                            <input type="text" id="nomor_identitas" name="nomor_identitas" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="scan_identitas">Scan Identitas <span class="required">*</span></label>
                            <div class="file-upload">
                                <input type="file" id="scan_identitas" name="scan_identitas" accept=".pdf,.jpg,.jpeg,.png" required>
                                <label for="scan_identitas" class="file-upload-label">
                                    Klik untuk upload file (PDF, JPG, PNG maks. 2MB)
                                </label>
                            </div>
                            <div class="file-info">Format: PDF, JPG, PNG | Maks: 2MB</div>
                        </div>
                    </div>
                </div>
                
                <!-- Detail Permohonan -->
                <div class="form-section">
                    <h2 class="section-title">Detail Permohonan Informasi</h2>
                    
                    <div class="form-group">
                        <label for="tujuan_perangkat">Perangkat Daerah Tujuan <span class="required">*</span></label>
                        <select id="tujuan_perangkat" name="tujuan_perangkat" required>
                            <option value="">-- Pilih Perangkat Daerah --</option>
                            <option value="Sekretariat Daerah">Sekretariat Daerah</option>
                            <option value="Inspektorat">Inspektorat</option>
                            <option value="Dinas Pendidikan">Dinas Pendidikan</option>
                            <option value="Dinas Kesehatan">Dinas Kesehatan</option>
                            <option value="Dinas Pekerjaan Umum">Dinas Pekerjaan Umum</option>
                            <option value="Dinas Perhubungan">Dinas Perhubungan</option>
                            <option value="Dinas Sosial">Dinas Sosial</option>
                            <option value="Dinas Pertanian">Dinas Pertanian</option>
                            <option value="Dinas Perindustrian">Dinas Perindustrian</option>
                            <option value="Dinas Perdagangan">Dinas Perdagangan</option>
                            <option value="Dinas Kependudukan">Dinas Kependudukan</option>
                            <option value="Dinas Lingkungan Hidup">Dinas Lingkungan Hidup</option>
                            <option value="Dinas Kebudayaan dan Pariwisata">Dinas Kebudayaan dan Pariwisata</option>
                            <option value="Dinas Komunikasi dan Informatika">Dinas Komunikasi dan Informatika</option>
                            <option value="Dinas Perpustakaan dan Kearsipan">Dinas Perpustakaan dan Kearsipan</option>
                            <option value="Dinas Ketahanan Pangan">Dinas Ketahanan Pangan</option>
                            <option value="Dinas Tenaga Kerja">Dinas Tenaga Kerja</option>
                            <option value="Dinas Pemberdayaan Masyarakat">Dinas Pemberdayaan Masyarakat</option>
                            <option value="Dinas Perumahan">Dinas Perumahan</option>
                            <option value="Dinas Kelautan dan Perikanan">Dinas Kelautan dan Perikanan</option>
                            <option value="Satuan Polisi Pamong Praja">Satuan Polisi Pamong Praja</option>
                            <option value="Badan Perencanaan">Badan Perencanaan</option>
                            <option value="Badan Keuangan">Badan Keuangan</option>
                            <option value="Badan Kepegawaian">Badan Kepegawaian</option>
                            <option value="Badan Penanggulangan Bencana">Badan Penanggulangan Bencana</option>
                            <option value="Kecamatan">Kecamatan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tujuan_informasi">Tujuan Penggunaan Informasi <span class="required">*</span></label>
                        <textarea id="tujuan_informasi" name="tujuan_informasi" rows="4" required placeholder="Jelaskan tujuan penggunaan informasi yang Anda minta..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="cara_mendapatkan">Cara Mendapatkan Informasi <span class="required">*</span></label>
                        <textarea id="cara_mendapatkan" name="cara_mendapatkan" rows="4" required placeholder="Jelaskan cara Anda mendapatkan informasi yang diminta..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="cara_pengambilan">Cara Pengambilan Informasi <span class="required">*</span></label>
                        <select id="cara_pengambilan" name="cara_pengambilan" required>
                            <option value="">-- Pilih Cara Pengambilan --</option>
                            <option value="Langsung di Kantor PPID">Langsung di Kantor PPID</option>
                            <option value="Melalui Email">Melalui Email</option>
                            <option value="Melalui Pos">Melalui Pos</option>
                            <option value="Kurir">Kurir</option>
                        </select>
                    </div>
                </div>
                
                <div class="info-box">
                    <h4>Informasi Penting</h4>
                    <p>Berdasarkan UU No. 14 Tahun 2008, permohonan informasi akan diproses dalam waktu maksimal 10 hari kerja sejak diterima. Pastikan data yang Anda berikan lengkap dan benar.</p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Kirim Permohonan</button>
                    <button type="reset" class="btn-reset">Reset Form</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>PPID Kabupaten Dompu</h3>
                    <p>Pejabat Pengelola Informasi dan Dokumentasi<br>Kabupaten Dompu</p>
                </div>
                <div class="footer-section">
                    <h4>Kontak</h4>
                    <p>Email: ppid@dompukab.go.id<br>Telepon: (0371) XXXXX<br>Fax: (0371) XXXXX</p>
                </div>
                <div class="footer-section">
                    <h4>Alamat</h4>
                    <p>Jl. Lombok No. 1<br>Kota Dompu, NTB<br>Indonesia</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> PPID Kabupaten Dompu. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="/ppid_dompu/js/script.js"></script>
</body>
</html>
