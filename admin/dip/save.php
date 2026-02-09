<?php
require_once '../../includes/config.php';
require_once '../../includes/security_headers.php';
require_once '../../includes/file_upload.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$csrf = (string)($_POST['csrf_token'] ?? '');
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    header('Location: index.php?error=' . urlencode('Sesi tidak valid. Silakan coba lagi.'));
    exit();
}

$action = (string)($_POST['action'] ?? 'save');
$dip_id = (int)($_POST['dip_id'] ?? 0);

if ($action === 'delete') {
    if ($dip_id > 0) {
        $stmt = $conn->prepare("DELETE FROM daftar_informasi_publik WHERE id = ?");
        $stmt->bind_param("i", $dip_id);
        if ($stmt->execute()) {
            logAdminAction('DELETE_DIP', 'daftar_informasi_publik', $dip_id, "Deleted DIP ID: $dip_id");
            header('Location: index.php?success=' . urlencode('Data DIP berhasil dihapus.'));
            exit();
        }
    }
    header('Location: index.php?error=' . urlencode('Gagal menghapus data DIP.'));
    exit();
}

$judul = trim((string)($_POST['judul'] ?? ''));
$ringkasan = trim((string)($_POST['ringkasan'] ?? ''));
$kategori = (string)($_POST['kategori'] ?? '');
$tahun = isset($_POST['tahun']) && $_POST['tahun'] !== '' ? (int)$_POST['tahun'] : null;
$status = (string)($_POST['status_publikasi'] ?? 'published');
$existingFileId = (int)($_POST['existing_file_id'] ?? 0);

$errors = [];
if ($judul === '') {
    $errors[] = 'Judul wajib diisi.';
}
if (!in_array($kategori, ['berkala', 'serta-merta', 'setiap-saat'], true)) {
    $errors[] = 'Kategori tidak valid.';
}
if (!in_array($status, ['published', 'draft'], true)) {
    $errors[] = 'Status publikasi tidak valid.';
}

$fileId = $existingFileId;
if (!empty($_FILES['lampiran']['name'])) {
    $uploadHandler = new FileUploadHandler();
    $upload = $uploadHandler->handleUpload('lampiran', 'file');
    if (!$upload['success']) {
        $errors[] = $upload['error'] ?? 'Gagal upload file.';
    } else {
        $filename = $upload['filename'];
        $original = $upload['original_name'] ?? '';
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $size = isset($_FILES['lampiran']['size']) ? (int)$_FILES['lampiran']['size'] : 0;

        $stmt = $conn->prepare("INSERT INTO files (nama_file, path, tipe_file, ukuran, uploaded_by) VALUES (?, ?, ?, ?, ?)");
        $path = 'uploads/' . $filename;
        $uploadedBy = (int)$_SESSION['admin_id'];
        $stmt->bind_param("sssii", $original, $path, $ext, $size, $uploadedBy);
        if ($stmt->execute()) {
            $fileId = (int)$conn->insert_id;
        } else {
            $errors[] = 'Gagal menyimpan metadata file.';
        }
    }
}

if (!empty($errors)) {
    header('Location: index.php?error=' . urlencode($errors[0]));
    exit();
}

if ($dip_id > 0) {
    $stmt = $conn->prepare("
        UPDATE daftar_informasi_publik
        SET judul = ?, ringkasan = ?, kategori = ?, tahun = ?, file_id = ?, status_publikasi = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("sssissi", $judul, $ringkasan, $kategori, $tahun, $fileId, $status, $dip_id);
    if ($stmt->execute()) {
        logAdminAction('UPDATE_DIP', 'daftar_informasi_publik', $dip_id, "Updated DIP: $judul");
        header('Location: index.php?success=' . urlencode('Data DIP berhasil diperbarui.'));
        exit();
    }
    header('Location: index.php?error=' . urlencode('Gagal memperbarui data DIP.'));
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO daftar_informasi_publik (judul, ringkasan, kategori, tahun, file_id, status_publikasi, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("sssiss", $judul, $ringkasan, $kategori, $tahun, $fileId, $status);
if ($stmt->execute()) {
    $inserted = (int)$conn->insert_id;
    logAdminAction('CREATE_DIP', 'daftar_informasi_publik', $inserted, "Created DIP: $judul");
    header('Location: index.php?success=' . urlencode('Data DIP berhasil disimpan.'));
    exit();
}

header('Location: index.php?error=' . urlencode('Gagal menyimpan data DIP.'));
?>
