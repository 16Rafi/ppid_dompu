<?php
require_once '../includes/config.php';

// Get page content based on slug
$page_slug = isset($_GET['slug']) ? cleanInput($_GET['slug']) : 'profil';
$page = null;

$stmt = $conn->prepare("SELECT * FROM pages WHERE slug = ? LIMIT 1");
$stmt->bind_param("s", $page_slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $page = $result->fetch_assoc();
} else {
    // Default content if page not found
    $page = [
        'title' => 'Halaman Tidak Ditemukan',
        'content' => '<p>Halaman yang Anda cari tidak ditemukan.</p>',
        'meta_description' => ''
    ];
}

$meta_description = $page['meta_description'] ?? substr(strip_tags($page['content'] ?? ''), 0, 160);

function renderPageBlocks(int $pageId, mysqli $conn): void
{
    $stmt = $conn->prepare("
        SELECT id, type 
        FROM page_blocks 
        WHERE page_id = ? 
        ORDER BY position ASC
    ");
    $stmt->bind_param("i", $pageId);
    $stmt->execute();
    $blocks = $stmt->get_result();

    while ($block = $blocks->fetch_assoc()) {
        switch ($block['type']) {
            case 'text':
                renderTextBlock($block['id'], $conn);
                break;
            case 'table':
                renderTableBlock($block['id'], $conn);
                break;
            case 'file':
                renderFileBlock($block['id'], $conn);
                break;
            case 'link':
                renderLinkBlock($block['id'], $conn);
                break;
            case 'image':
                renderImageBlock($block['id'], $conn);
                break;
        }
    }
}

function pageHasBlocks(int $pageId, mysqli $conn): bool
{
    $stmt = $conn->prepare("SELECT 1 FROM page_blocks WHERE page_id = ? LIMIT 1");
    $stmt->bind_param("i", $pageId);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res && $res->num_rows > 0;
}

function renderTextBlock(int $blockId, mysqli $conn): void
{
    $stmt = $conn->prepare("
        SELECT content 
        FROM page_text_blocks 
        WHERE block_id = ?
    ");
    $stmt->bind_param("i", $blockId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row) {
        echo '<div class="page-text">';
        echo nl2br($row['content']);
        echo '</div>';
    }
}

function renderTableBlock(int $blockId, mysqli $conn): void
{
    $stmt = $conn->prepare("
        SELECT enable_search, enable_sort
        FROM page_table_blocks
        WHERE block_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $blockId);
    $stmt->execute();
    $settings = $stmt->get_result()->fetch_assoc();
    $enableSearch = isset($settings['enable_search']) ? (int)$settings['enable_search'] : 1;
    $enableSort = isset($settings['enable_sort']) ? (int)$settings['enable_sort'] : 1;

    $cols = $conn->query("
        SELECT id, header_name 
        FROM page_table_columns 
        WHERE table_block_id = $blockId 
        ORDER BY column_order
    ");

    $rows = $conn->query("
        SELECT id 
        FROM page_table_rows 
        WHERE table_block_id = $blockId 
        ORDER BY row_order
    ");

    echo '<div class="page-table-wrapper" data-search="' . $enableSearch . '" data-sort="' . $enableSort . '">';
    if ($enableSearch) {
        echo '<div class="page-table-tools">';
        echo '<input type="text" class="page-table-search" placeholder="Cari...">';
        echo '</div>';
    }
    echo '<table class="page-table">';
    echo '<thead><tr>';

    $columns = [];
    while ($c = $cols->fetch_assoc()) {
        $columns[] = $c;
        echo '<th>' . htmlspecialchars($c['header_name']) . '</th>';
    }

    echo '</tr></thead><tbody>';

    while ($r = $rows->fetch_assoc()) {
        echo '<tr>';
        foreach ($columns as $c) {
            $cellStmt = $conn->prepare("
                SELECT cell_value 
                FROM page_table_cells 
                WHERE row_id = ? AND column_id = ?
            ");
            $cellStmt->bind_param("ii", $r['id'], $c['id']);
            $cellStmt->execute();
            $cell = $cellStmt->get_result()->fetch_assoc();
            echo '<td>' . renderTableCellValue($cell['cell_value'] ?? '') . '</td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}

function renderFileBlock(int $blockId, mysqli $conn): void
{
    $stmt = $conn->prepare("
        SELECT file_path, file_name 
        FROM page_files 
        WHERE block_id = ?
    ");
    $stmt->bind_param("i", $blockId);
    $stmt->execute();
    $f = $stmt->get_result()->fetch_assoc();

    if ($f) {
        echo '<div class="page-file">';
        echo '<a href="' . htmlspecialchars(buildUrl($f['file_path'])) . '" download>';
        echo '📄 ' . htmlspecialchars($f['file_name']);
        echo '</a></div>';
    }
}

function renderLinkBlock(int $blockId, mysqli $conn): void
{
    $stmt = $conn->prepare("
        SELECT label, url, target 
        FROM page_links 
        WHERE block_id = ?
    ");
    $stmt->bind_param("i", $blockId);
    $stmt->execute();
    $l = $stmt->get_result()->fetch_assoc();

    if ($l) {
        echo '<div class="page-link">';
        echo '<a href="' . htmlspecialchars($l['url']) . '" target="' . $l['target'] . '">';
        echo htmlspecialchars($l['label']);
        echo '</a></div>';
    }
}

function renderTableCellValue(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    if (preg_match('/^(link|file|image|img):\s*(.+)$/i', $value, $matches)) {
        $type = strtolower($matches[1]);
        $payload = $matches[2];
        $parts = array_map('trim', explode('|', $payload));

        if ($type === 'link' && count($parts) >= 2) {
            $label = $parts[0];
            $url = $parts[1];
            return '<a href="' . htmlspecialchars(buildUrl($url)) . '" target="_blank" rel="noopener noreferrer">'
                . htmlspecialchars($label) . '</a>';
        }

        if ($type === 'file' && count($parts) >= 2) {
            $label = $parts[0];
            $path = $parts[1];
            return '<a href="' . htmlspecialchars(buildUrl($path)) . '" download>'
                . htmlspecialchars($label) . '</a>';
        }

        if (($type === 'image' || $type === 'img') && count($parts) >= 2) {
            $alt = $parts[0];
            $path = $parts[1];
            return '<img src="' . htmlspecialchars(buildImageUrl($path)) . '" alt="' . htmlspecialchars($alt) . '" style="max-width: 180px; height: auto;">';
        }
    }

    return htmlspecialchars($value);
}

function renderImageBlock(int $blockId, mysqli $conn): void
{
    $stmt = $conn->prepare("
        SELECT image_path, alt_text, caption
        FROM page_image_blocks
        WHERE block_id = ?
    ");
    $stmt->bind_param("i", $blockId);
    $stmt->execute();
    $img = $stmt->get_result()->fetch_assoc();

    if ($img) {
        $src = buildImageUrl($img['image_path']);
        $alt = htmlspecialchars($img['alt_text'] ?? '');
        $caption = trim((string)($img['caption'] ?? ''));
        echo '<figure class="page-image">';
        echo '<img src="' . htmlspecialchars($src) . '" alt="' . $alt . '">';
        if ($caption !== '') {
            echo '<figcaption>' . htmlspecialchars($caption) . '</figcaption>';
        }
        echo '</figure>';
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['title']); ?> - PPID Kabupaten Dompu</title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="page-template">
    <?php include '../includes/header.php'; ?>

    <!-- Page Content -->
    <main class="page-content" style="margin-top: 100px; padding: 60px 0;">
        <div class="container">
            <div class="page-header">
                <h1><?php echo htmlspecialchars($page['title']); ?></h1>
                <nav class="breadcrumb">
                    <a href="<?php echo buildUrl('index.php'); ?>">Beranda</a>
                    <span class="separator">/</span>
                    <span class="current"><?php echo htmlspecialchars($page['title']); ?></span>
                </nav>
            </div>
            
            <div class="page-body">
                <div class="content-wrapper">
                    <?php
                    if (!empty($page['id']) && pageHasBlocks((int)$page['id'], $conn)) {
                        renderPageBlocks((int)$page['id'], $conn);
                    } else {
                        echo html_entity_decode((string)($page['content'] ?? ''), ENT_QUOTES, 'UTF-8');
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>



