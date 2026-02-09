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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'load') {
    $page_id = (int)($_GET['page_id'] ?? 0);
    if ($page_id <= 0) {
        respondJson(['success' => false, 'message' => 'Invalid page ID'], 400);
    }

    $stmt = $conn->prepare("SELECT id, title, slug, content FROM pages WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $page_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        respondJson(['success' => false, 'message' => 'Page not found'], 404);
    }
    $page = $res->fetch_assoc();

    $blocks = [];
    $stmt = $conn->prepare("SELECT id, type FROM page_blocks WHERE page_id = ? ORDER BY position ASC");
    $stmt->bind_param("i", $page_id);
    $stmt->execute();
    $blockRes = $stmt->get_result();
    while ($block = $blockRes->fetch_assoc()) {
        $blockId = (int)$block['id'];
        $type = (string)$block['type'];
        $data = ['type' => $type];

        switch ($type) {
            case 'text':
                $b = $conn->prepare("SELECT content FROM page_text_blocks WHERE block_id = ?");
                $b->bind_param("i", $blockId);
                $b->execute();
                $row = $b->get_result()->fetch_assoc();
                $data['content'] = $row['content'] ?? '';
                break;
            case 'table':
                $table = [
                    'headers' => [],
                    'rows' => [],
                    'enable_search' => 1,
                    'enable_sort' => 1
                ];
                $b = $conn->prepare("SELECT enable_search, enable_sort FROM page_table_blocks WHERE block_id = ?");
                $b->bind_param("i", $blockId);
                $b->execute();
                $row = $b->get_result()->fetch_assoc();
                if ($row) {
                    $table['enable_search'] = (int)$row['enable_search'];
                    $table['enable_sort'] = (int)$row['enable_sort'];
                }

                $cols = $conn->query("SELECT id, header_name FROM page_table_columns WHERE table_block_id = $blockId ORDER BY column_order");
                $colIds = [];
                while ($c = $cols->fetch_assoc()) {
                    $colIds[] = (int)$c['id'];
                    $table['headers'][] = $c['header_name'];
                }

                $rowsRes = $conn->query("SELECT id FROM page_table_rows WHERE table_block_id = $blockId ORDER BY row_order");
                while ($r = $rowsRes->fetch_assoc()) {
                    $rowId = (int)$r['id'];
                    $rowData = [];
                    foreach ($colIds as $colId) {
                        $cellStmt = $conn->prepare("SELECT cell_value FROM page_table_cells WHERE row_id = ? AND column_id = ?");
                        $cellStmt->bind_param("ii", $rowId, $colId);
                        $cellStmt->execute();
                        $cell = $cellStmt->get_result()->fetch_assoc();
                        $rowData[] = $cell['cell_value'] ?? '';
                    }
                    $table['rows'][] = $rowData;
                }

                $data['table'] = $table;
                break;
            case 'file':
                $b = $conn->prepare("SELECT file_path, file_name FROM page_files WHERE block_id = ?");
                $b->bind_param("i", $blockId);
                $b->execute();
                $row = $b->get_result()->fetch_assoc();
                $data['file_path'] = $row['file_path'] ?? '';
                $data['file_name'] = $row['file_name'] ?? '';
                break;
            case 'link':
                $b = $conn->prepare("SELECT label, url, target FROM page_links WHERE block_id = ?");
                $b->bind_param("i", $blockId);
                $b->execute();
                $row = $b->get_result()->fetch_assoc();
                $data['label'] = $row['label'] ?? '';
                $data['url'] = $row['url'] ?? '';
                $data['target'] = $row['target'] ?? '_self';
                break;
            case 'image':
                $b = $conn->prepare("SELECT image_path, alt_text, caption FROM page_image_blocks WHERE block_id = ?");
                $b->bind_param("i", $blockId);
                $b->execute();
                $row = $b->get_result()->fetch_assoc();
                $data['image_path'] = $row['image_path'] ?? '';
                $data['alt_text'] = $row['alt_text'] ?? '';
                $data['caption'] = $row['caption'] ?? '';
                break;
        }

        $blocks[] = $data;
    }

    $legacyContent = trim((string)($page['content'] ?? ''));

    respondJson([
        'success' => true,
        'page' => [
            'id' => (int)$page['id'],
            'title' => (string)$page['title'],
            'slug' => (string)$page['slug']
        ],
        'blocks' => $blocks,
        'legacy_content' => $legacyContent
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(['success' => false, 'message' => 'Invalid request'], 405);
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)$csrf)) {
    respondJson(['success' => false, 'message' => 'CSRF token invalid'], 403);
}

if ($action !== 'save') {
    respondJson(['success' => false, 'message' => 'Invalid action'], 400);
}

$title = trim((string)($_POST['title'] ?? ''));
$slug = trim((string)($_POST['slug'] ?? ''));
$page_id = (int)($_POST['page_id'] ?? 0);
$blocks_json = (string)($_POST['blocks'] ?? '[]');

if ($title === '') {
    respondJson(['success' => false, 'message' => 'Judul halaman wajib diisi'], 400);
}
if ($slug === '') {
    respondJson(['success' => false, 'message' => 'Slug wajib diisi'], 400);
}
if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
    respondJson(['success' => false, 'message' => 'Slug hanya boleh huruf kecil, angka, dan dash (-)'], 400);
}

$stmt = $conn->prepare("SELECT id FROM pages WHERE slug = ? AND id != ? LIMIT 1");
$stmt->bind_param("si", $slug, $page_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    respondJson(['success' => false, 'message' => 'Slug sudah digunakan'], 400);
}

$blocks = json_decode($blocks_json, true);
if (!is_array($blocks)) {
    respondJson(['success' => false, 'message' => 'Format blocks tidak valid'], 400);
}

$isUpdate = $page_id > 0;
if ($isUpdate) {
    $stmt = $conn->prepare("UPDATE pages SET title = ?, slug = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $title, $slug, $page_id);
    if (!$stmt->execute()) {
        respondJson(['success' => false, 'message' => 'Gagal memperbarui halaman'], 500);
    }
} else {
    $stmt = $conn->prepare("INSERT INTO pages (title, slug, content, created_at, updated_at) VALUES (?, ?, '', NOW(), NOW())");
    $stmt->bind_param("ss", $title, $slug);
    if (!$stmt->execute()) {
        respondJson(['success' => false, 'message' => 'Gagal membuat halaman'], 500);
    }
    $page_id = (int)$conn->insert_id;
}

// Remove existing blocks (cascade delete)
$stmt = $conn->prepare("DELETE FROM page_blocks WHERE page_id = ?");
$stmt->bind_param("i", $page_id);
$stmt->execute();

$uploadHandler = new FileUploadHandler();
$allowedTypes = ['text', 'table', 'file', 'link', 'image'];

foreach ($blocks as $index => $block) {
    $type = (string)($block['type'] ?? '');
    if (!in_array($type, $allowedTypes, true)) {
        continue;
    }

    if ($type === 'link') {
        $label = trim((string)($block['label'] ?? ''));
        $url = trim((string)($block['url'] ?? ''));
        if ($label === '' || $url === '') {
            continue;
        }
    }

    if ($type === 'file') {
        $fileField = (string)($block['file_field'] ?? '');
        $existingPath = (string)($block['existing_path'] ?? '');
        $existingName = (string)($block['existing_name'] ?? '');
        $hasNewUpload = $fileField !== '' && isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] !== UPLOAD_ERR_NO_FILE;
        if (!$hasNewUpload && ($existingPath === '' || $existingName === '')) {
            continue;
        }
    }

    if ($type === 'image') {
        $fileField = (string)($block['file_field'] ?? '');
        $existingPath = (string)($block['existing_path'] ?? '');
        $hasNewUpload = $fileField !== '' && isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] !== UPLOAD_ERR_NO_FILE;
        if (!$hasNewUpload && $existingPath === '') {
            continue;
        }
    }

    $position = (int)$index;
    $stmt = $conn->prepare("INSERT INTO page_blocks (page_id, type, position) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $page_id, $type, $position);
    if (!$stmt->execute()) {
        respondJson(['success' => false, 'message' => 'Gagal menyimpan block'], 500);
    }
    $block_id = (int)$conn->insert_id;

    if ($type === 'text') {
        $content = trim((string)($block['content'] ?? ''));
        $stmt = $conn->prepare("INSERT INTO page_text_blocks (block_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $block_id, $content);
        $stmt->execute();
        continue;
    }

    if ($type === 'link') {
        $label = trim((string)($block['label'] ?? ''));
        $url = trim((string)($block['url'] ?? ''));
        $target = (string)($block['target'] ?? '_self');
        if (!in_array($target, ['_self', '_blank'], true)) {
            $target = '_self';
        }
        $stmt = $conn->prepare("INSERT INTO page_links (block_id, label, url, target) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $block_id, $label, $url, $target);
        $stmt->execute();
        continue;
    }

    if ($type === 'table') {
        $enableSearch = !empty($block['enable_search']) ? 1 : 0;
        $enableSort = !empty($block['enable_sort']) ? 1 : 0;
        $stmt = $conn->prepare("INSERT INTO page_table_blocks (block_id, enable_search, enable_sort) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $block_id, $enableSearch, $enableSort);
        $stmt->execute();

        $headers = $block['headers'] ?? [];
        $rows = $block['rows'] ?? [];
        $columnIds = [];
        foreach ($headers as $hIndex => $header) {
            $header = trim((string)$header);
            if ($header === '') {
                $header = 'Kolom ' . ($hIndex + 1);
            }
            $order = (int)$hIndex;
            $stmt = $conn->prepare("INSERT INTO page_table_columns (table_block_id, header_name, column_order) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $block_id, $header, $order);
            $stmt->execute();
            $columnIds[] = (int)$conn->insert_id;
        }

        foreach ($rows as $rIndex => $row) {
            if (!is_array($row)) {
                continue;
            }
            $order = (int)$rIndex;
            $stmt = $conn->prepare("INSERT INTO page_table_rows (table_block_id, row_order) VALUES (?, ?)");
            $stmt->bind_param("ii", $block_id, $order);
            $stmt->execute();
            $rowId = (int)$conn->insert_id;

            foreach ($columnIds as $cIndex => $colId) {
                $value = (string)($row[$cIndex] ?? '');
                $cellStmt = $conn->prepare("INSERT INTO page_table_cells (row_id, column_id, cell_value) VALUES (?, ?, ?)");
                $cellStmt->bind_param("iis", $rowId, $colId, $value);
                $cellStmt->execute();
            }
        }
        continue;
    }

    if ($type === 'file') {
        $fileField = (string)($block['file_field'] ?? '');
        $existingPath = (string)($block['existing_path'] ?? '');
        $existingName = (string)($block['existing_name'] ?? '');
        $filePath = '';
        $fileName = '';

        if ($fileField !== '' && isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = $uploadHandler->handleUpload($fileField, 'file');
            if (!$upload['success']) {
                respondJson(['success' => false, 'message' => $upload['error']], 400);
            }
            $filePath = 'uploads/' . $upload['filename'];
            $fileName = $upload['original_name'];
        } elseif ($existingPath !== '' && $existingName !== '') {
            $filePath = $existingPath;
            $fileName = $existingName;
        } else {
            continue;
        }

        $stmt = $conn->prepare("INSERT INTO page_files (block_id, file_path, file_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $block_id, $filePath, $fileName);
        $stmt->execute();
        continue;
    }

    if ($type === 'image') {
        $fileField = (string)($block['file_field'] ?? '');
        $existingPath = (string)($block['existing_path'] ?? '');
        $altText = trim((string)($block['alt_text'] ?? ''));
        $caption = trim((string)($block['caption'] ?? ''));
        $imagePath = '';

        if ($fileField !== '' && isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = $uploadHandler->handleUpload($fileField, 'image');
            if (!$upload['success']) {
                respondJson(['success' => false, 'message' => $upload['error']], 400);
            }
            $imagePath = 'uploads/' . $upload['filename'];
        } elseif ($existingPath !== '') {
            $imagePath = $existingPath;
        } else {
            continue;
        }

        $stmt = $conn->prepare("INSERT INTO page_image_blocks (block_id, image_path, alt_text, caption) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $block_id, $imagePath, $altText, $caption);
        $stmt->execute();
        continue;
    }
}

if ($isUpdate) {
    logAdminAction('UPDATE_PAGE', 'page', $page_id, "Updated page: $title (ID: $page_id)");
} else {
    logAdminAction('CREATE_PAGE', 'page', $page_id, "Created page: $title (slug: $slug)");
}

respondJson([
    'success' => true,
    'page_id' => $page_id,
    'redirect' => $isUpdate ? 'index.php?updated=1' : 'index.php?created=1'
]);
