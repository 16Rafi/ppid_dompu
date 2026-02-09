<?php
require_once '../../includes/config.php';
require_once '../../includes/security_headers.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle delete action (POST only with CSRF validation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['menu_id'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $menu_id = (int)$_POST['menu_id'];
    
    // Verify menu exists before deletion
    $stmt = $conn->prepare("SELECT id FROM menus WHERE id = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        header('Location: index.php?error=Menu not found');
        exit();
    }
    
    // Log admin action
    logAdminAction('DELETE_MENU', 'menu', $menu_id, "Deleted menu ID: $menu_id");
    
    // Delete menu (child menus will become parent menus)
    $stmt = $conn->prepare("DELETE FROM menus WHERE id = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    
    header('Location: index.php?deleted=1');
    exit();
}

// Handle old GET delete attempts - redirect with error
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    header('Location: index.php?error=Invalid request method');
    exit();
}

// Get all menus with hierarchy
function getMenuHierarchy($conn) {
    $menus = [];
    $result = $conn->query("SELECT * FROM menus ORDER BY parent_id ASC, order_index ASC");
    
    while ($row = $result->fetch_assoc()) {
        $menus[$row['id']] = $row;
    }
    
    // Build hierarchy
    $hierarchy = [];
    foreach ($menus as $id => $menu) {
        if ($menu['parent_id'] == 0) {
            $hierarchy[$id] = $menu;
            $hierarchy[$id]['children'] = [];
            
            // Find children
            foreach ($menus as $child_id => $child) {
                if ($child['parent_id'] == $id) {
                    $hierarchy[$id]['children'][$child_id] = $child;
                }
            }
        }
    }
    
    return $hierarchy;
}

// Get all pages for dropdown
$pages = $conn->query("SELECT id, title, slug FROM pages ORDER BY title ASC");

$menu_hierarchy = getMenuHierarchy($conn);

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - PPID Admin</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body admin-menus">
    <!-- Header -->
   <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h2><a href="../dashboard.php" style="color: #FCFDFD;">PPID Admin</a></h2>
                </div>
                <nav class="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="../dashboard.php" class="nav-link">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a href="../permohonan/index.php" class="nav-link">Permohonan</a>
                        </li>
                        <li class="nav-item">
                            <a href="../keberatan/index.php" class="nav-link">Keberatan</a>
                        </li>
                        <li class="nav-item">
                            <a href="../dip/index.php" class="nav-link">DIP</a>
                        </li>
                        <li class="nav-item">
                            <a href="../pages/index.php" class="nav-link">Pages</a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">Menu</a>
                        </li>
                        <li class="nav-item">
                            <a href="../laporan.php" class="nav-link">Laporan</a>
                        </li>
                        <li class="nav-item">
                            <a href="../logout.php" class="nav-link">Logout</a>
                        </li>
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
    <main class="admin-page">
        <div class="page-header">
            <h1 class="page-title">Kelola Menu</h1>
            <button class="btn-primary" onclick="showAddMenuModal()">
                <span>➕</span>
                Tambah Menu
            </button>
        </div>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="success-message">
                    Menu berhasil dihapus!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) || !empty($_SESSION['errors'])): ?>
                <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <?php
                    $sessionErrors = $_SESSION['errors'] ?? [];
                    if (!empty($sessionErrors) && is_array($sessionErrors)) {
                        echo '<strong>Terjadi kesalahan:</strong><ul style="margin: 0.5rem 0 0 1.2rem;">';
                        foreach ($sessionErrors as $err) {
                            echo '<li>' . htmlspecialchars($err) . '</li>';
                        }
                        echo '</ul>';
                        unset($_SESSION['errors']);
                    } else {
                        switch($_GET['error']) {
                            case 'Menu not found':
                                echo 'Menu tidak ditemukan!';
                                break;
                            case 'Invalid request method':
                                echo 'Metode request tidak valid!';
                                break;
                            default:
                                echo 'Terjadi kesalahan saat menyimpan menu.';
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['updated'])): ?>
                <div class="success-message">
                    Menu berhasil diperbarui!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['created'])): ?>
                <div class="success-message">
                    Menu berhasil dibuat!
                </div>
            <?php endif; ?>

            <div class="section-card">
                <?php if (!empty($menu_hierarchy)): ?>
                    <ul class="menu-list" id="menuList">
                        <?php foreach ($menu_hierarchy as $parent_id => $parent_menu): ?>
                            <li class="menu-item" data-id="<?php echo $parent_menu['id']; ?>" data-parent="0">
                                <div class="menu-content">
                                    <div style="display: flex; align-items: center;">
                                        <span class="drag-handle">⋮⋮</span>
                                        <div class="menu-info">
                                            <div class="menu-name">
                                                <?php echo htmlspecialchars($parent_menu['name']); ?>
                                                <span class="menu-status <?php echo $parent_menu['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                    <?php echo $parent_menu['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                                </span>
                                            </div>
                                            <div class="menu-url"><?php echo htmlspecialchars($parent_menu['url']); ?></div>
                                        </div>
                                    </div>
                                    <div class="action-buttons">
                                        <button
                                            class="btn-success js-edit-menu"
                                            data-id="<?php echo (int)$parent_menu['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($parent_menu['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-url="<?php echo htmlspecialchars($parent_menu['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-parent="<?php echo (int)$parent_menu['parent_id']; ?>"
                                            data-active="<?php echo (int)$parent_menu['is_active']; ?>"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="menu_id" value="<?php echo $parent_menu['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <button type="submit" class="btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <?php if (!empty($parent_menu['children'])): ?>
                                    <ul class="child-menus">
                                        <?php foreach ($parent_menu['children'] as $child_id => $child_menu): ?>
                                            <li class="menu-item child-menu-item" data-id="<?php echo $child_menu['id']; ?>" data-parent="<?php echo $parent_menu['id']; ?>">
                                                <div class="menu-content">
                                                    <div style="display: flex; align-items: center;">
                                                        <span class="drag-handle">⋮⋮</span>
                                                        <div class="menu-info">
                                                            <div class="menu-name">
                                                                <?php echo htmlspecialchars($child_menu['name']); ?>
                                                                <span class="menu-status <?php echo $child_menu['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                                    <?php echo $child_menu['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                                                </span>
                                                            </div>
                                                            <div class="menu-url"><?php echo htmlspecialchars($child_menu['url']); ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="action-buttons">
                                                        <button
                                                            class="btn-success js-edit-menu"
                                                            data-id="<?php echo (int)$child_menu['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($child_menu['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-url="<?php echo htmlspecialchars($child_menu['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-parent="<?php echo (int)$child_menu['parent_id']; ?>"
                                                            data-active="<?php echo (int)$child_menu['is_active']; ?>"
                                                        >
                                                            Edit
                                                        </button>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="menu_id" value="<?php echo $child_menu['id']; ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                            <button type="submit" class="btn-danger">Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>📋 Belum Ada Menu</h3>
                        <p>Belum ada menu yang dibuat. Klik tombol "Tambah Menu" untuk membuat menu pertama.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Save Order Button -->
    <button class="save-order-btn hidden" id="saveOrderBtn" onclick="saveMenuOrder()">
        💾 Simpan Urutan Menu
    </button>

    <!-- Menu Modal -->
    <div id="menuModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMenuModal()">&times;</span>
            <h2 style="color: #093A5A; margin-bottom: 1.5rem;" id="modalTitle">Tambah Menu</h2>
            
            <form id="menuForm" method="POST" action="save.php">
                <input type="hidden" id="menuId" name="menu_id" value="">
                
                <div class="form-group">
                    <label for="menuName">Nama Menu *</label>
                    <input type="text" id="menuName" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="parentMenu">Parent Menu</label>
                    <select id="parentMenu" name="parent_id" class="form-control">
                        <option value="0">Menu Utama (Tidak ada parent)</option>
                        <?php foreach ($menu_hierarchy as $parent_id => $parent_menu): ?>
                            <option value="<?php echo $parent_menu['id']; ?>">
                                <?php echo htmlspecialchars($parent_menu['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="linkType">Tipe Link</label>
                    <select id="linkType" name="link_type" class="form-control" onchange="toggleLinkFields()">
                        <option value="page">Halaman</option>
                        <option value="manual">URL Manual</option>
                    </select>
                </div>
                
                <div class="form-group" id="pageSelectDiv">
                    <label for="pageSelect">Pilih Halaman</label>
                    <select id="pageSelect" name="page_id" class="form-control">
                        <option value="">-- Pilih Halaman --</option>
                        <?php 
                        // Reset the pages result pointer
                        $pages->data_seek(0);
                        while ($page = $pages->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $page['id']; ?>" data-slug="<?php echo htmlspecialchars($page['slug']); ?>">
                                <?php echo htmlspecialchars($page['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group" id="manualUrl" style="display: none;">
                    <label for="manualUrlInput">URL Manual</label>
                    <input type="text" id="manualUrlInput" name="url" class="form-control" placeholder="contoh: /about">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="isActive" name="is_active" value="1" checked>
                        <label for="isActive" style="margin: 0;">Menu Aktif</label>
                    </div>
                </div>
                
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" class="btn-primary">Simpan Menu</button>
                    <button type="button" class="btn-secondary" onclick="closeMenuModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>
    <script src='../../js/script.js'></script>
</body>
</html>


