<?php
require_once '../../includes/config.php';
require_once '../../includes/security_headers.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $page_id = (int)$_GET['id'];
    
    // Log admin action
    logAdminAction('DELETE_PAGE', 'page', $page_id, "Deleted page ID: $page_id");
    
    // Delete page
    $stmt = $conn->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->bind_param("i", $page_id);
    $stmt->execute();
    
    header('Location: index.php?deleted=1');
    exit();
}

// Get all pages
$pages = $conn->query("SELECT id, title, slug, created_at, updated_at FROM pages ORDER BY updated_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Halaman - PPID Admin</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body admin-pages-index">
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h2><a href="../dashboard.php" style="color: #FCFDFD;">PPID Admin</a></h2>
                </div>
                <nav class="nav-menu">
                    <ul class="nav-list">
                        <li><a href="../dashboard.php" class="nav-link">Dashboard</a></li>
                        <li><a href="../permohonan/index.php" class="nav-link">Permohonan</a></li>
                        <li><a href="../keberatan/index.php" class="nav-link">Keberatan</a></li>
                        <li><a href="../dip/index.php" class="nav-link">DIP</a></li>
                        <li><a href="index.php" class="nav-link active">Pages</a></li>
                        <li><a href="../menus/index.php" class="nav-link">Menu</a></li>
                        <li><a href="../laporan.php" class="nav-link">Laporan</a></li>
                        <li><a href="../logout.php" class="nav-link">Logout</a></li>
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
            <h1 class="page-title">Kelola Halaman</h1>
            <a href="create.php" class="btn-primary">
                <span>➕</span>
                Tambah Halaman
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="success-message">
                Halaman berhasil dihapus!
            </div>
        <?php endif; ?>

        <div class="section-card">
            <?php if ($pages->num_rows > 0): ?>
                <table class="pages-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Slug</th>
                            <th>Terakhir Diubah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($page = $pages->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($page['title']); ?></strong>
                                </td>
                                <td>
                                    <code><?php echo htmlspecialchars($page['slug']); ?></code>
                                </td>
                                <td>
                                    <?php 
                                    $updated = new DateTime($page['updated_at']);
                                    echo $updated->format('d/m/Y H:i');
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit.php?id=<?php echo $page['id']; ?>" class="btn-edit">
                                            Edit
                                        </a>
                                        <a href="index.php?action=delete&id=<?php echo $page['id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus halaman ini?')">
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>📄 Belum Ada Halaman</h3>
                    <p>Belum ada halaman yang dibuat. Klik tombol "Tambah Halaman" untuk membuat halaman pertama.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../../js/script.js"></script>
</body>
</html>
