<?php
require_once '../includes/config.php';
require_once '../includes/security_headers.php';

if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

// Check if user is logged in as admin
$is_admin = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']) && $_SESSION['admin_role'] === 'admin';

if (isset($_GET['success']) && $_GET['success'] !== '') {
    $success_message = (string)$_GET['success'];
}
if (isset($_GET['error']) && $_GET['error'] !== '') {
    $error_message = (string)$_GET['error'];
}

// Handle form submission for adding news
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $posted_csrf = (string)($_POST['csrf_token'] ?? '');
    $session_csrf = (string)($_SESSION['csrf_token'] ?? '');
    if ($posted_csrf === '' || $session_csrf === '' || !hash_equals($session_csrf, $posted_csrf)) {
        $return_page = max(1, (int)($_POST['return_page'] ?? 1));
        header('Location: berita.php?page=' . $return_page . '&error=' . urlencode('Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.'));
        exit();
    }

    $action = (string)($_POST['action'] ?? 'add');
    $return_page = max(1, (int)($_POST['return_page'] ?? 1));

    if ($action === 'delete') {
        $news_id = (int)($_POST['news_id'] ?? 0);
        if ($news_id > 0) {
            $stmt = $conn->prepare('DELETE FROM news WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $news_id);
            if ($stmt->execute()) {
                logAdminAction('DELETE_NEWS', 'news', $news_id, "Deleted news with ID: $news_id");
                header('Location: berita.php?page=' . $return_page . '&success=' . urlencode('Berita berhasil dihapus!'));
                exit();
            }
        }
        header('Location: berita.php?page=' . $return_page . '&error=' . urlencode('Terjadi kesalahan saat menghapus berita.'));
        exit();
    }

    $title = (string)($_POST['title'] ?? '');
    $content_raw = (string)($_POST['content'] ?? '');
    $content = sanitizeHtmlContent($content_raw);
    $excerpt = substr(strip_tags($content), 0, 200) . '...';
    $slug = generateSlug($title);

    $category = 'berita';
    if ($has_category) {
        $category_selected = trim((string)($_POST['category'] ?? ''));
        $category_new = trim((string)($_POST['category_new'] ?? ''));
        $category = ($category_new !== '') ? $category_new : ($category_selected !== '' ? $category_selected : 'berita');
    }

    $image = (string)($_POST['existing_image'] ?? '');
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $upload_result = validateAndMoveUpload($_FILES['image'], $upload_dir);
        if ($upload_result['success']) {
            $image = $upload_result['filename'];
        } else {
            $return_page = max(1, (int)($_POST['return_page'] ?? 1));
            header('Location: berita.php?page=' . $return_page . '&error=' . urlencode($upload_result['error']));
            exit();
        }
    }

    if ($action === 'update') {
        $news_id = (int)($_POST['news_id'] ?? 0);
        if ($news_id > 0) {
            if ($has_category) {
                $stmt = $conn->prepare("UPDATE news SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, {$category_col} = ?, updated_at = NOW() WHERE id = ? LIMIT 1");
                $stmt->bind_param('ssssssi', $title, $slug, $excerpt, $content, $image, $category, $news_id);
            } else {
                $stmt = $conn->prepare('UPDATE news SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
                $stmt->bind_param('sssssi', $title, $slug, $excerpt, $content, $image, $news_id);
            }
            if ($stmt->execute()) {
                logAdminAction('UPDATE_NEWS', 'news', $news_id, "Updated news: $title");
                header('Location: berita.php?page=' . $return_page . '&success=' . urlencode('Berita berhasil diperbarui!'));
                exit();
            }
        }
        header('Location: berita.php?page=' . $return_page . '&error=' . urlencode('Terjadi kesalahan saat memperbarui berita.'));
        exit();
    }

    if ($has_category) {
        $stmt = $conn->prepare("INSERT INTO news (title, slug, excerpt, content, image, {$category_col}, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'published', NOW(), NOW())");
        $stmt->bind_param('ssssss', $title, $slug, $excerpt, $content, $image, $category);
    } else {
        $stmt = $conn->prepare("INSERT INTO news (title, slug, excerpt, content, image, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'published', NOW(), NOW())");
        $stmt->bind_param('sssss', $title, $slug, $excerpt, $content, $image);
    }

    if ($stmt->execute()) {
        $inserted_id = $stmt->insert_id;
        logAdminAction('ADD_NEWS', 'news', $inserted_id, "Added new news: $title");
        header('Location: berita.php?page=' . $return_page . '&success=' . urlencode('Berita berhasil dipublish!'));
        exit();
    }
    header('Location: berita.php?page=' . $return_page . '&error=' . urlencode('Terjadi kesalahan saat menyimpan berita.'));
    exit();
}

$edit_news = null;
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
if ($is_admin && $edit_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM news WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        $edit_news = $res->fetch_assoc();
    }
}

$existing_categories = [];
if ($has_category) {
    $cat_res = $conn->query("SELECT DISTINCT {$category_col} AS category FROM news WHERE {$category_col} IS NOT NULL AND {$category_col} <> '' ORDER BY {$category_col}");
    if ($cat_res) {
        while ($row = $cat_res->fetch_assoc()) {
            $existing_categories[] = (string)($row['category'] ?? '');
        }
    }
}

// Pagination settings
$news_per_page = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $news_per_page;

// Get total news count
$total_query = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
$total_result = $conn->query($total_query);
$total_news = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_news / $news_per_page);

// Get news for current page
$news_query = "SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT $offset, $news_per_page";
$news_result = $conn->query($news_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita - PPID Kabupaten Dompu</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-img">PPID</div>
                    <h2>PPID</h2>
                </div>
                <nav class="nav-menu">
                    <ul class="nav-list">
                        <?php
                        $menu_query = "SELECT * FROM menus WHERE parent_id = 0 AND is_active = 1 ORDER BY order_index";
                        $menu_result = $conn->query($menu_query);
                        
                        while ($menu = $menu_result->fetch_assoc()) {
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

    <div class="berita-page">
        <div class="berita-header">
            <h1>Berita Dompu</h1>
            <div class="berita-divider"></div>
        </div>

        <?php if ($is_admin): ?>
        <div class="admin-section">
            <div class="admin-header">
                <h3><?php echo $edit_news ? 'Edit Berita' : 'Panel Admin'; ?></h3>
                <div style="display:flex; gap:10px; align-items:center;">
                    <a href="<?php echo buildUrl('admin/dashboard.php'); ?>" class="add-news-btn" style="text-decoration:none;">Dashboard</a>
                    <?php if ($edit_news): ?>
                        <a href="<?php echo 'berita.php?page=' . $page; ?>" class="add-news-btn" style="text-decoration:none;">Batal Edit</a>
                    <?php else: ?>
                        <button class="add-news-btn" onclick="toggleForm()">+ Tambahkan Berita</button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <div class="news-form" id="newsForm"<?php echo $edit_news ? ' style="display:block;"' : ''; ?>>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $edit_news ? 'update' : 'add'; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="return_page" value="<?php echo (int)$page; ?>">
                    <?php if ($edit_news): ?>
                        <input type="hidden" name="news_id" value="<?php echo (int)$edit_news['id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars((string)($edit_news['image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="title">Judul Berita</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars((string)($edit_news['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <?php if ($has_category): ?>
                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <select id="category" name="category">
                            <?php
                            $selected_category = $has_category ? (string)($edit_news[$category_col] ?? '') : '';
                            if ($selected_category === '') {
                                $selected_category = 'berita';
                            }
                            $options = array_unique(array_filter($existing_categories, function ($v) {
                                return $v !== '';
                            }));
                            if (!in_array('berita', $options, true)) {
                                array_unshift($options, 'berita');
                            }
                            foreach ($options as $opt) {
                                $opt_safe = htmlspecialchars((string)$opt, ENT_QUOTES, 'UTF-8');
                                $is_selected = ((string)$opt === (string)$selected_category) ? ' selected' : '';
                                echo '<option value="' . $opt_safe . '"' . $is_selected . '>' . $opt_safe . '</option>';
                            }
                            ?>
                        </select>
                        <input type="text" name="category_new" placeholder="Atau ketik kategori baru" value="">
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="image">Gambar atau Video (MP4)</label>
                        <input type="file" id="image" name="image" accept="image/*,video/mp4">
                        <small style="color:#666; font-size:0.85rem; margin-top:4px; display:block;">Maks: gambar 5 MB, video MP4 50 MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Narasi Berita</label>
                        <textarea id="content" name="content" required><?php echo htmlspecialchars((string)($edit_news['content'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-publish"><?php echo $edit_news ? 'Simpan Perubahan' : 'Publish'; ?></button>
                        <?php if (!$edit_news): ?>
                            <button type="button" class="btn-cancel" onclick="toggleForm()">Batal</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="berita-list">
            <?php
            if ($news_result && $news_result->num_rows > 0) {
                while ($news = $news_result->fetch_assoc()) {
                    echo '<div class="berita-item">';
                    echo '<div class="berita-img">';
                    if (!empty($news['image'])) {
                        echo '<img src="' . buildImageUrl($news['image']) . '" alt="' . htmlspecialchars($news['title']) . '">';
                    } else {
                        echo '<img src="https://via.placeholder.com/200x140/7392A8/FFFFFF?text=Berita" alt="' . htmlspecialchars($news['title']) . '">';
                    }
                    echo '</div>';
                    echo '<div class="berita-content">';
                    echo '<div class="berita-meta">';
                    $cat_label = 'Berita';
                    if ($has_category && isset($news[$category_col]) && (string)$news[$category_col] !== '') {
                        $cat_label = (string)$news[$category_col];
                    }
                    echo '<span class="berita-category">' . htmlspecialchars($cat_label, ENT_QUOTES, 'UTF-8') . '</span>';
                    echo '<span class="berita-date">' . formatDate($news['created_at']) . '</span>';
                    echo '</div>';
                    echo '<h2 class="berita-title"><a href="' . buildUrl('pages/berita-detail.php?slug=' . $news['slug']) . '">' . htmlspecialchars($news['title']) . '</a></h2>';
                    echo '<div class="berita-excerpt">' . htmlspecialchars($news['excerpt']) . '</div>';
                    echo '<a href="' . buildUrl('pages/berita-detail.php?slug=' . $news['slug']) . '" class="berita-more">Selengkapnya</a>';
                    
                    if ($is_admin) {
                        echo '<div style="margin-top:12px; display:flex; gap:10px; align-items:center;">';
                        echo '<a class="add-news-btn" style="padding:8px 14px; text-decoration:none; background:#7392A8;" href="berita.php?edit_id=' . (int)$news['id'] . '&page=' . (int)$page . '">Edit</a>';
                        echo '<form method="POST" style="margin:0;">';
                        echo '<input type="hidden" name="action" value="delete">';
                        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars((string)($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8') . '">';
                        echo '<input type="hidden" name="news_id" value="' . (int)$news['id'] . '">';
                        echo '<input type="hidden" name="return_page" value="' . (int)$page . '">';
                        echo '<button type="submit" class="add-news-btn" style="padding:8px 14px; background:#e74c3c;" onclick="return confirm(\'Hapus berita ini?\');">Hapus</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 12px; border: 2px dashed #ddd;">';
                echo '<h3 style="color: #666; margin-bottom: 12px;">Belum ada berita</h3>';
                echo '<p style="color: #999;">Belum ada berita yang dipublish. Silakan tambahkan berita melalui panel admin.</p>';
                if ($is_admin) {
                    echo '<button class="add-news-btn" onclick="toggleForm()" style="margin-top: 20px;">+ Tambahkan Berita Pertama</button>';
                }
                echo '</div>';
            }
            ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            // Previous button
            if ($page > 1):
                $prev_page = $page - 1;
                echo '<a href="?page=' . $prev_page . '" class="pagination-link">&laquo; Prev</a>';
            endif;
            
            // Page numbers
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            if ($start_page > 1):
                echo '<a href="?page=1" class="pagination-link">1</a>';
                if ($start_page > 2):
                    echo '<span class="pagination-dots">...</span>';
                endif;
            endif;
            
            for ($i = $start_page; $i <= $end_page; $i++):
                if ($i == $page):
                    echo '<span class="pagination-current">' . $i . '</span>';
                else:
                    echo '<a href="?page=' . $i . '" class="pagination-link">' . $i . '</a>';
                endif;
            endfor;
            
            if ($end_page < $total_pages):
                if ($end_page < $total_pages - 1):
                    echo '<span class="pagination-dots">...</span>';
                endif;
                echo '<a href="?page=' . $total_pages . '" class="pagination-link">' . $total_pages . '</a>';
            endif;
            
            // Next button
            if ($page < $total_pages):
                $next_page = $page + 1;
                echo '<a href="?page=' . $next_page . '" class="pagination-link">Next &raquo;</a>';
            endif;
            ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>PPID Kabupaten Dompu</h3>
                    <p>Pejabat Pengelola Informasi dan Dokumentasi Kabupaten Dompu</p>
                </div>
                <div class="footer-section">
                    <h4>Kontak</h4>
                    <p>Email: ppid@dompukab.go.id</p>
                    <p>Telepon: (0371) 123456</p>
                </div>
                <div class="footer-section">
                    <h4>Alamat</h4>
                    <p>Jl. Soekarno Hatta No. 1<br>Kecamatan Dompu<br>Kabupaten Dompu</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> PPID Kabupaten Dompu. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>
