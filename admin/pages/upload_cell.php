<?php
require_once '../../includes/config.php';
require_once '../../includes/security_headers.php';
require_once '../../includes/file_upload.php';

header('Content-Type: application/json; charset=UTF-8');

function respondJson($payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload);
    exit();
}

if (!isset($_SESSION['admin_id'])) {
    respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(['success' => false, 'message' => 'Invalid request'], 405);
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)$csrf)) {
    respondJson(['success' => false, 'message' => 'CSRF token invalid'], 403);
}

$type = $_POST['type'] ?? 'file';
if (!in_array($type, ['file', 'image'], true)) {
    respondJson(['success' => false, 'message' => 'Invalid type'], 400);
}

if (!isset($_FILES['file'])) {
    respondJson(['success' => false, 'message' => 'No file uploaded'], 400);
}

$uploadHandler = new FileUploadHandler();
$upload = $uploadHandler->handleUpload('file', $type);
if (!$upload['success']) {
    respondJson(['success' => false, 'message' => $upload['error'] ?? 'Upload failed'], 400);
}

respondJson([
    'success' => true,
    'path' => 'uploads/' . $upload['filename'],
    'name' => $upload['original_name'] ?? ''
]);
