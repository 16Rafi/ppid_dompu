<?php
require_once '../includes/config.php';
require_once '../includes/permohonan_service.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

function tableExists($conn, $tableName) {
    $stmt = $conn->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1");
    $stmt->bind_param("s", $tableName);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res && $res->num_rows > 0;
}

function safeCount($conn, $tableName) {
    if (!tableExists($conn, $tableName)) {
        return 0;
    }
    $q = $conn->query("SELECT COUNT(*) as count FROM `{$tableName}`");
    if (!$q) {
        return 0;
    }
    $row = $q->fetch_assoc();
    return (int)($row['count'] ?? 0);
}

function safeRecent($conn, $tableName, $limit = 5) {
    if (!tableExists($conn, $tableName)) {
        return null;
    }
    $limit = max(1, (int)$limit);
    $q = $conn->query("SELECT title, created_at FROM `{$tableName}` ORDER BY created_at DESC LIMIT {$limit}");
    return $q ?: null;
}

// Get dashboard statistics
$total_news = safeCount($conn, 'news');
$total_pages = safeCount($conn, 'pages');
$total_documents = safeCount($conn, 'documents');
$total_links = safeCount($conn, 'external_links');

// Get permohonan statistics
$permohonan_stats = [
    'total' => 0,
    'diajukan' => 0,
    'diproses' => 0,
    'selesai' => 0,
    'ditolak' => 0
];

if (tableExists($conn, 'permohonan_informasi')) {
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM permohonan_informasi GROUP BY status");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $permohonan_stats[$row['status']] = $row['count'];
        $permohonan_stats['total'] += $row['count'];
    }
}

// Get recent permohonan
$recent_permohonan = null;
if (tableExists($conn, 'permohonan_informasi')) {
    $q = $conn->query("SELECT nomor_registrasi, nama_pemohon, status, created_at FROM permohonan_informasi ORDER BY created_at DESC LIMIT 5");
    $recent_permohonan = $q ?: null;
}

// Get recent news
$recent_news = safeRecent($conn, 'news', 5);

// Get recent pages
$recent_pages = safeRecent($conn, 'pages', 5);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - PPID Kabupaten Dompu</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body admin-dashboard">
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><a href="<?php echo buildUrl('index.php'); ?>" style="color: #FCFDFD;">PPID Admin</a></h2>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="<?php echo buildUrl('admin/dashboard.php'); ?>" class="active">Dashboard</a></li>
                        <li><a href="<?php echo buildUrl('admin/permohonan/index.php'); ?>">📋 Daftar Permohonan</a></li>
                        <li><a href="<?php echo buildUrl('admin/keberatan/index.php'); ?>">📝 Daftar Keberatan</a></li>
                        <li><a href="<?php echo buildUrl('admin/dip/index.php'); ?>">📄 Kelola DIP</a></li>
                    <li><a href="<?php echo buildUrl('admin/pages/index.php'); ?>">📄 Kelola Pages</a></li>
                    <li><a href="<?php echo buildUrl('admin/menus/index.php'); ?>">🗂️ Kelola Menu</a></li>
                    <li><a href="<?php echo buildUrl('admin/laporan.php'); ?>">📊 Laporan</a></li>
                    <li><a href="<?php echo buildUrl('pages/berita.php'); ?>">Kelola Berita</a></li>
                    <li><a href="<?php echo buildUrl('admin/logout.php'); ?>">Keluar</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Selamat datang, <strong><?php echo $_SESSION['username']; ?></strong></span>
                    <a href="<?php echo buildUrl('admin/logout.php'); ?>" class="btn-logout">Keluar</a>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_news; ?></div>
                    <div class="stat-label">Total Berita</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_pages; ?></div>
                    <div class="stat-label">Total Halaman</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_documents; ?></div>
                    <div class="stat-label">Total Dokumen</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_links; ?></div>
                    <div class="stat-label">Total Link</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #093A5A, #7392A8); color: white;">
                    <div class="stat-number" style="color: white;"><?php echo $permohonan_stats['total']; ?></div>
                    <div class="stat-label" style="color: #F4B800;">Total Permohonan</div>
                </div>
                <div class="stat-card" style="background: #fff3cd; border: 2px solid #F4B800;">
                    <div class="stat-number" style="color: #856404;"><?php echo $permohonan_stats['diajukan']; ?></div>
                    <div class="stat-label" style="color: #856404;">Menunggu Proses</div>
                </div>
                <div class="stat-card" style="background: #d4edda; border: 2px solid #28a745;">
                    <div class="stat-number" style="color: #155724;"><?php echo $permohonan_stats['selesai']; ?></div>
                    <div class="stat-label" style="color: #155724;">Selesai</div>
                </div>
                <div class="stat-card" style="background: #f8d7da; border: 2px solid #dc3545;">
                    <div class="stat-number" style="color: #721c24;"><?php echo $permohonan_stats['ditolak']; ?></div>
                    <div class="stat-label" style="color: #721c24;">Ditolak</div>
                </div>
            </div>
            
            <!-- Recent Content -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">Berita Terbaru</div>
                    <div class="card-content">
                        <ul class="recent-list">
                            <?php if ($recent_news): ?>
                                <?php while ($news = $recent_news->fetch_assoc()): ?>
                                    <li>
                                        <span class="recent-title"><?php echo htmlspecialchars($news['title']); ?></span>
                                        <span class="recent-date"><?php echo formatDate($news['created_at']); ?></span>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li>
                                    <span class="recent-title">Tidak ada data</span>
                                    <span class="recent-date"></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">Halaman Terbaru</div>
                    <div class="card-content">
                        <ul class="recent-list">
                            <?php if ($recent_pages): ?>
                                <?php while ($page = $recent_pages->fetch_assoc()): ?>
                                    <li>
                                        <span class="recent-title"><?php echo htmlspecialchars($page['title']); ?></span>
                                        <span class="recent-date"><?php echo formatDate($page['created_at']); ?></span>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li>
                                    <span class="recent-title">Tidak ada data</span>
                                    <span class="recent-date"></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="dashboard-card" style="border: 2px solid #093A5A;">
                    <div class="card-header" style="background: #093A5A; color: #FCFDFD;">
                        📋 Permohonan Terbaru
                        <a href="<?php echo buildUrl('admin/permohonan/index.php'); ?>" style="float: right; color: #F4B800; text-decoration: none; font-size: 0.9rem;">Lihat Semua →</a>
                    </div>
                    <div class="card-content">
                        <ul class="recent-list">
                            <?php if ($recent_permohonan): ?>
                                <?php while ($permohonan = $recent_permohonan->fetch_assoc()): ?>
                                    <li>
                                        <div style="flex: 1;">
                                            <div class="recent-title" style="margin-bottom: 0.25rem;">
                                                <?php echo htmlspecialchars($permohonan['nomor_registrasi']); ?>
                                            </div>
                                            <div style="color: #666; font-size: 0.85rem;">
                                                <?php echo htmlspecialchars($permohonan['nama_pemohon']); ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right;">
                                            <span class="recent-date" style="display: block; margin-bottom: 0.25rem;">
                                                <?php echo date('d/m', strtotime($permohonan['created_at'])); ?>
                                            </span>
                                            <span style="
                                                display: inline-block;
                                                padding: 0.2rem 0.5rem;
                                                border-radius: 12px;
                                                font-size: 0.7rem;
                                                font-weight: 600;
                                                text-transform: uppercase;
                                                <?php
                                                $statusColors = [
                                                    'diajukan' => 'background: #fff3cd; color: #856404;',
                                                    'diproses' => 'background: #cce5ff; color: #004085;',
                                                    'selesai' => 'background: #d4edda; color: #155724;',
                                                    'ditolak' => 'background: #f8d7da; color: #721c24;'
                                                ];
                                                echo $statusColors[$permohonan['status']] ?? '';
                                                ?>
                                            ">
                                                <?php 
                                                $statusLabels = [
                                                    'diajukan' => 'Diajukan',
                                                    'diproses' => 'Diproses', 
                                                    'selesai' => 'Selesai',
                                                    'ditolak' => 'Ditolak'
                                                ];
                                                echo $statusLabels[$permohonan['status']] ?? $permohonan['status'];
                                                ?>
                                            </span>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li>
                                    <span class="recent-title">Belum ada permohonan</span>
                                    <span class="recent-date"></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($permohonan_stats['total'] > 0): ?>
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                                <a href="<?php echo buildUrl('admin/permohonan/index.php'); ?>" style="
                                    display: block;
                                    text-align: center;
                                    background: #093A5A;
                                    color: #FCFDFD;
                                    padding: 0.75rem;
                                    border-radius: 6px;
                                    text-decoration: none;
                                    font-weight: 600;
                                    transition: all 0.3s ease;
                                " onmouseover="this.style.background='#7392A8'" onmouseout="this.style.background='#093A5A'">
                                    Kelola <?php echo $permohonan_stats['total']; ?> Permohonan
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src='../js/script.js'></script>
</body>
</html>




