<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php';
require_once '../includes/email_config.php';
require_once '../includes/file_upload.php';
require_once '../includes/email_service.php';
require_once '../includes/keberatan_service.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/html; charset=UTF-8');

$fileHandler = new FileUploadHandler();
$emailService = new EmailService();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $uploadedFile = null;
    $uploadedFilePath = '';

    $required_fields = [
        'nama_lengkap',
        'identitas',
        'no_identitas',
        'informasi_diminta',
        'alasan_pengajuan'
    ];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Field " . str_replace('_', ' ', $field) . " wajib diisi";
        }
    }

    if (empty($_FILES['scan_identitas']['name'])) {
        $errors[] = "Scan identitas wajib diupload";
    }

    if (empty($errors)) {
        if (!empty($_FILES['scan_identitas']['name'])) {
            $upload = $fileHandler->handleUpload('scan_identitas', 'file');
            if (!$upload['success']) {
                $errors[] = "Upload file gagal: " . $upload['error'];
            } else {
                $uploadedFile = $upload['original_name'];
                $uploadedFilePath = $upload['filepath'];
                $scanPath = 'uploads/' . $upload['filename'];
            }
        }
    }

    if (empty($errors)) {
        $keberatanData = [
            'nama_lengkap' => $_POST['nama_lengkap'],
            'identitas' => $_POST['identitas'],
            'no_identitas' => $_POST['no_identitas'],
            'scan_identitas' => $scanPath ?? '',
            'informasi_diminta' => $_POST['informasi_diminta'],
            'alasan_pengajuan' => $_POST['alasan_pengajuan'],
            'keterangan_tambahan' => $_POST['keterangan_tambahan'] ?? ''
        ];

        $validationErrors = validateKeberatanData($keberatanData);
        if (!empty($validationErrors)) {
            $errors = array_merge($errors, $validationErrors);
        }

        if (empty($errors)) {
            $sanitizedData = sanitizeKeberatanData($keberatanData);
            $saveResult = saveKeberatan($sanitizedData);

            if ($saveResult['success']) {
                $registrationNumber = $saveResult['nomor_registrasi'];
                $recipientEmail = EmailConfig::getRecipientEmail();
                $subject = 'Pengajuan Keberatan - ' . htmlspecialchars($_POST['nama_lengkap']) . ' - ' . $registrationNumber;

                $emailTemplate = '
                <html>
                <head>
                    <title>Pengajuan Keberatan</title>
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
                            <h2>PPID Kabupaten Dompu</h2>
                            <h3>Pengajuan Keberatan</h3>
                        </div>

                        <div class="content">
                            <div class="section">
                                <p><strong>Tanggal Pengajuan:</strong> ' . date('d/m/Y H:i') . '</p>
                                <p><strong>No. Registrasi:</strong> ' . $registrationNumber . '</p>
                            </div>

                            <div class="section">
                                <h4>Data Pemohon</h4>
                                <table>
                                    <tr><td class="label-td">Nama Lengkap</td><td>' . htmlspecialchars($_POST['nama_lengkap']) . '</td></tr>
                                    <tr><td class="label-td">Identitas</td><td>' . htmlspecialchars($_POST['identitas']) . '</td></tr>
                                    <tr><td class="label-td">No Identitas</td><td>' . htmlspecialchars($_POST['no_identitas']) . '</td></tr>
                                </table>
                            </div>

                            <div class="section">
                                <h4>Detail Keberatan</h4>
                                <table>
                                    <tr><td class="label-td">Informasi Diminta</td><td>' . htmlspecialchars($_POST['informasi_diminta']) . '</td></tr>
                                    <tr><td class="label-td">Alasan Pengajuan</td><td>' . htmlspecialchars($_POST['alasan_pengajuan']) . '</td></tr>
                                    <tr><td class="label-td">Keterangan Tambahan</td><td>' . htmlspecialchars($_POST['keterangan_tambahan'] ?? '-') . '</td></tr>
                                </table>
                            </div>';

                if ($uploadedFile) {
                    $emailTemplate .= '
                            <div class="section">
                                <h4>Dokumen Lampiran</h4>
                                <p><strong>Nama File:</strong> ' . htmlspecialchars($uploadedFile) . '</p>
                            </div>';
                }

                $emailTemplate .= '
                            <div class="success-box">
                                <strong>✅ Pengajuan Keberatan Diterima</strong><br>
                                Pengajuan keberatan Anda telah diterima dan akan diproses sesuai ketentuan yang berlaku.
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

                $attachments = [];
                if ($uploadedFilePath) {
                    $attachments[] = [
                        'path' => $uploadedFilePath,
                        'name' => $uploadedFile
                    ];
                }

                $sendResult = $emailService->sendEmail(
                    $recipientEmail,
                    $subject,
                    $emailTemplate,
                    strip_tags(str_replace(['<br>', '</p>', '</tr>'], ["\n", "\n\n", "\n"], $emailTemplate)),
                    $attachments
                );

                if ($sendResult['success']) {
                    $notification = '<div class="alert alert-success">
                        <h3>✅ Keberatan Berhasil Dikirim!</h3>
                        <p>Pengajuan keberatan Anda telah kami terima dengan nomor registrasi:</p>
                        <p><strong>' . $registrationNumber . '</strong></p>
                        <p>Konfirmasi telah dikirim ke email PPID.</p>
                        <p><small>ID Transaksi: ' . ($sendResult['technical_details']['message_id'] ?? 'N/A') . '</small></p>
                    </div>';
                } else {
                    $notification = '<div class="alert alert-success">
                        <h3>✅ Keberatan Berhasil Disimpan!</h3>
                        <p>Keberatan Anda telah disimpan dengan nomor registrasi: <strong>' . $registrationNumber . '</strong></p>
                        <p>Notifikasi email gagal dikirim, namun data Anda tersimpan.</p>
                    </div>';
                }

                if ($isAjax) {
                    echo $notification;
                    exit;
                }
            } else {
                $errors[] = "Gagal menyimpan keberatan: " . $saveResult['error'];
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
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Keberatan - PPID Kabupaten Dompu</title>
    <meta name="description" content="Formulir pengajuan keberatan PPID Kabupaten Dompu">
    <link rel="stylesheet" href="/ppid_dompu/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="permohonan-page">
        <div class="page-header">
            <h1>Pengajuan Keberatan</h1>
            <p>Isi formulir berikut untuk mengajukan keberatan atas permohonan informasi publik.</p>
        </div>

        <div class="form-container permohonan-container">
            <form method="POST" enctype="multipart/form-data" id="keberatanForm">
                <div class="form-section">
                    <h2 class="section-title">Identitas Pemohon</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="form-group">
                            <label for="identitas">Identitas <span class="required">*</span></label>
                            <select id="identitas" name="identitas" required>
                                <option value="">-- Pilih Identitas --</option>
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
                            <label for="no_identitas">No Identitas <span class="required">*</span></label>
                            <input type="text" id="no_identitas" name="no_identitas" required>
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

                <div class="form-section">
                    <h2 class="section-title">Detail Keberatan</h2>

                    <div class="form-group">
                        <label for="informasi_diminta">Informasi yang Diminta <span class="required">*</span></label>
                        <textarea id="informasi_diminta" name="informasi_diminta" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Alasan Pengajuan Keberatan <span class="required">*</span></label>
                        <div class="radio-group">
                            <?php
                            $alasanOptions = [
                                'Permohonan Informasi Di Tolak',
                                'Informasi Berkala Tidak Disediakan',
                                'Permintaan Informasi Tidak Ditanggapi',
                                'Permintaan Informasi Ditanggapi Tidak Sebagaimana Yang Diminta',
                                'Permintaan Informasi Tidak Dipenuhi',
                                'Biaya Yang Dikenakan Tidak Wajar',
                                'Informasi Disampaikan Melebihi Jangka Waktu Yang Ditentukan',
                                'Penyalahgunaan Wewenang Pejabat PPID'
                            ];
                            foreach ($alasanOptions as $idx => $label):
                                $value = htmlspecialchars($label);
                            ?>
                                <label class="radio-item">
                                    <input type="radio" name="alasan_pengajuan" value="<?php echo $value; ?>" required>
                                    <span><?php echo $value; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="keterangan_tambahan">Keterangan Tambahan</label>
                        <textarea id="keterangan_tambahan" name="keterangan_tambahan" rows="3"></textarea>
                    </div>
                </div>

                <div class="info-box">
                    <h4>Informasi Penting</h4>
                    <p>Pengajuan keberatan akan diproses sesuai ketentuan yang berlaku. Pastikan data yang Anda berikan lengkap dan benar.</p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Kirim Keberatan</button>
                    <button type="reset" class="btn-reset">Reset Form</button>
                </div>
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>



