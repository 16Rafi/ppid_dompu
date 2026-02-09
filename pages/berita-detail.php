<?php
require_once '../includes/config.php';
require_once '../includes/security_headers.php';

$category_col = null;
$col_stmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'news' AND column_name IN ('kategori','category')");
$col_stmt->execute();
$col_res = $col_stmt->get_result();
if ($col_res) {
    $cols = [];
    while ($row = $col_res->fetch_assoc()) {
        $cols[] = (string)($row['column_name'] ?? '');
    }
    if (in_array('kategori', $cols, true)) {
        $category_col = 'kategori';
    } elseif (in_array('category', $cols, true)) {
        $category_col = 'category';
    }
}
$has_category = $category_col !== null;

// Get news detail
$slug = isset($_GET['slug']) ? cleanInput($_GET['slug']) : '';
$news = null;

if ($slug) {
    $stmt = $conn->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published'");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $news = $result->fetch_assoc();
    }
}

if (!$news) {
    header('Location: ' . buildUrl('pages/berita.php'));
    exit();
}

$news_category = 'Berita';
if ($has_category && isset($news[$category_col]) && (string)$news[$category_col] !== '') {
    $news_category = (string)$news[$category_col];
}

// Get related news
if ($has_category) {
    $related_query = "SELECT * FROM news WHERE id != ? AND status = 'published' AND {$category_col} = ? ORDER BY created_at DESC LIMIT 3";
    $related_stmt = $conn->prepare($related_query);
    $related_stmt->bind_param("is", $news['id'], $news_category);
} else {
    $related_query = "SELECT * FROM news WHERE id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3";
    $related_stmt = $conn->prepare($related_query);
    $related_stmt->bind_param("i", $news['id']);
}
$related_stmt->execute();
$related_result = $related_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - PPID Kabupaten Dompu</title>
    <meta name="description" content="<?php echo htmlspecialchars($news['excerpt']); ?>">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- News Detail -->
    <main class="news-detail" style="margin-top: 100px; padding: 60px 0;">
        <div class="container">
            <div class="news-header">
                <nav class="breadcrumb">
                    <a href="<?php echo buildUrl('index.php'); ?>">Beranda</a>
                    <span class="separator">/</span>
                    <a href="<?php echo buildUrl('pages/berita.php'); ?>">Berita</a>
                    <span class="separator">/</span>
                    <span class="current"><?php echo htmlspecialchars($news['title']); ?></span>
                </nav>

                <h1><?php echo htmlspecialchars($news['title']); ?></h1>

                <div class="news-meta">
                    <span class="category"><?php echo htmlspecialchars($news_category, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="date"><?php echo formatDate($news['created_at']); ?></span>
                </div>
            </div>
            
            <?php if ($news['image']): ?>
                <div class="featured-image">
                    <img src="<?php echo buildImageUrl($news['image']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>">
                </div>
            <?php endif; ?>
            
            <div class="news-content">
                <?php echo $news['content']; ?>
            </div>
            
            <?php if ($related_result->num_rows > 0): ?>
                <div class="related-news">
                    <h3>Berita Terkait</h3>
                    <div class="related-list">
                        <?php while ($related = $related_result->fetch_assoc()): ?>
                            <div class="related-item">
                                <div class="related-image">
                                    <?php if ($related['image']): ?>
                                        <img src="<?php echo buildImageUrl($related['image']); ?>" alt="<?php echo $related['title']; ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/200x150/7392A8/FFFFFF?text=Berita" alt="<?php echo $related['title']; ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="related-content">
                                    <h4><a href="<?php echo buildUrl('pages/berita-detail.php?slug=' . $related['slug']); ?>"><?php echo $related['title']; ?></a></h4>
                                    <p class="related-excerpt"><?php echo $related['excerpt']; ?></p>
                                    <span class="related-date"><?php echo formatDate($related['created_at']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>



