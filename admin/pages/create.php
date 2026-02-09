<?php
require_once '../../includes/config.php';
require_once '../../includes/security_headers.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Halaman - PPID Admin</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body admin-pages-create">
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
                        <li><a href="../laporan.php" class="nav-link">Laporan</a></li>
                        <li><a href="../permohonan/index.php" class="nav-link">Permohonan</a></li>
                        <li><a href="../keberatan/index.php" class="nav-link">Keberatan</a></li>
                        <li><a href="../dip/index.php" class="nav-link">DIP</a></li>
                        <li><a href="index.php" class="nav-link">Halaman</a></li>
                        <li><a href="../menus/index.php" class="nav-link">Menu</a></li>
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
            <h1 class="page-title">Tambah Halaman Baru</h1>
        </div>

        <div class="section-card">
            <form id="pageForm" method="POST" action="api.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="page_id" id="pageId" value="">

                <div class="form-group">
                    <label for="title">Judul Halaman *</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="slug">Slug *</label>
                    <input type="text" id="slug" name="slug" class="form-control" placeholder="contoh: tentang-kami" required>
                    <div class="help-text">
                        Slug akan digunakan dalam URL. Hanya boleh mengandung huruf kecil, angka, dan dash (-).
                    </div>
                    <div class="help-text">
                        URL publik: <code>pages/template.php?slug=slug-anda</code>
                    </div>
                </div>

                <div class="page-blocks">
                    <div class="blocks-toolbar">
                        <button type="button" class="btn-secondary" data-add-block="text">+ Teks</button>
                        <button type="button" class="btn-secondary" data-add-block="table">+ Tabel</button>
                        <button type="button" class="btn-secondary" data-add-block="file">+ File</button>
                        <button type="button" class="btn-secondary" data-add-block="link">+ Link</button>
                        <button type="button" class="btn-secondary" data-add-block="image">+ Gambar</button>
                    </div>

                    <div id="pageBlocks" class="blocks-list"></div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan Halaman</button>
                    <a href="index.php" class="btn-secondary">Batal</a>
                </div>
                <div id="pageSaveStatus" class="help-text"></div>
            </form>
        </div>
    </main>

    <script src="../../js/script.js"></script>
</body>
</html>



