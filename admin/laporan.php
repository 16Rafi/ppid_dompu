<?php
require_once '../includes/config.php';
require_once '../includes/permohonan_service.php';
require_once '../includes/keberatan_service.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Get statistics
$stats = [
    'total' => 0,
    'diajukan' => 0,
    'diproses' => 0,
    'selesai' => 0,
    'ditolak' => 0
];

$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM permohonan_informasi GROUP BY status");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
    $stats['total'] += $row['count'];
}

// Get monthly statistics
$monthlyStats = [];
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
        SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
        SUM(CASE WHEN status IN ('diajukan', 'diproses') THEN 1 ELSE 0 END) as pending
    FROM permohonan_informasi 
    WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $monthlyStats[] = $row;
}

// Get recent activity
$recentActivity = [];
$stmt = $conn->prepare("
    SELECT pi.*, ls.status_baru, ls.waktu as status_waktu
    FROM permohonan_informasi pi
    LEFT JOIN log_status_permohonan ls ON pi.id = ls.permohonan_id
    ORDER BY COALESCE(ls.waktu, pi.created_at) DESC
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentActivity[] = $row;
}

// Keberatan stats
$keberatanStats = [
    'total' => 0,
    'diajukan' => 0,
    'diproses' => 0,
    'selesai' => 0,
    'ditolak' => 0
];

$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM keberatan GROUP BY status");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $keberatanStats[$row['status']] = $row['count'];
    $keberatanStats['total'] += $row['count'];
}

$keberatanRecent = [];
$stmt = $conn->prepare("
    SELECT k.*, lk.status_baru, lk.waktu as status_waktu
    FROM keberatan k
    LEFT JOIN log_status_keberatan lk ON k.id = lk.keberatan_id
    ORDER BY COALESCE(lk.waktu, k.tanggal_pengajuan) DESC
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $keberatanRecent[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Permohonan - Admin PPID</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body admin-laporan">
    <!-- Header -->
   <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h2><a href="dashboard.php" style="color: #FCFDFD;">PPID Admin</a></h2>
                </div>
                <nav class="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a href="permohonan/index.php" class="nav-link">Permohonan</a>
                        </li>
                        <li class="nav-item">
                            <a href="keberatan/index.php" class="nav-link">Keberatan</a>
                        </li>
                        <li class="nav-item">
                            <a href="dip/index.php" class="nav-link">DIP</a>
                        </li>
                        <li class="nav-item">
                            <a href="pages/index.php" class="nav-link">Pages</a>
                        </li>
                        <li class="nav-item">
                            <a href="menus/index.php" class="nav-link">Menu</a>
                        </li>
                        <li class="nav-item">
                            <a href="laporan.php" class="nav-link active">Laporan</a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">Logout</a>
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
            <h1 class="page-title">Laporan Permohonan Informasi</h1>
            <div class="admin-info">
                <span>Admin: <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="export_laporan.php" class="btn-export">Export Laporan</a>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Permohonan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['diajukan']; ?></div>
                <div class="stat-label">Diajukan</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $stats['total'] > 0 ? ($stats['diajukan'] / $stats['total']) * 100 : 0; ?>%;"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['diproses']; ?></div>
                <div class="stat-label">Diproses</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $stats['total'] > 0 ? ($stats['diproses'] / $stats['total']) * 100 : 0; ?>%;"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['selesai']; ?></div>
                <div class="stat-label">Selesai</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $stats['total'] > 0 ? ($stats['selesai'] / $stats['total']) * 100 : 0; ?>%;"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['ditolak']; ?></div>
                <div class="stat-label">Ditolak</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $stats['total'] > 0 ? ($stats['ditolak'] / $stats['total']) * 100 : 0; ?>%;"></div>
                </div>
            </div>
        </div>

        <!-- Monthly Statistics -->
        <div class="section-card">
            <h2 class="section-title">📈 Statistik Bulanan</h2>
            <div class="table-container">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Total</th>
                            <th>Selesai</th>
                            <th>Ditolak</th>
                            <th>Menunggu</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthlyStats as $stat): ?>
                            <tr>
                                <td><?php 
                                    $date = DateTime::createFromFormat('Y-m', $stat['month']);
                                    echo $date ? $date->format('F Y') : $stat['month'];
                                ?></td>
                                <td><?php echo $stat['total']; ?></td>
                                <td><?php echo $stat['selesai']; ?></td>
                                <td><?php echo $stat['ditolak']; ?></td>
                                <td><?php echo $stat['pending']; ?></td>
                                <td>
                                    <?php 
                                    $completionRate = $stat['total'] > 0 ? (($stat['selesai'] + $stat['ditolak']) / $stat['total']) * 100 : 0;
                                    echo number_format($completionRate, 1) . '%';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="section-card">
            <h2 class="section-title">🕐 Aktivitas Terkini</h2>
            <div class="activity-list">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon <?php echo $activity['status_baru'] ?: 'diajukan'; ?>">
                            <?php 
                            $statusIcons = [
                                'diajukan' => '📝',
                                'diproses' => '⚙️',
                                'selesai' => '✅',
                                'ditolak' => '❌'
                            ];
                            echo $statusIcons[$activity['status_baru']] ?? '📝';
                            ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <?php echo htmlspecialchars($activity['nomor_registrasi']); ?> - 
                                <?php echo htmlspecialchars($activity['nama_pemohon']); ?>
                            </div>
                            <div class="activity-time">
                                <?php 
                                $time = $activity['status_waktu'] ?: $activity['created_at'];
                                echo date('d/m/Y H:i', strtotime($time)); 
                                ?> - 
                                Status: <?php echo htmlspecialchars($activity['status_baru'] ?: 'Diajukan'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    
        <!-- Keberatan Panel -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">Laporan Keberatan</h2>
                <a href="export_laporan_keberatan.php" class="btn-export">Export Keberatan</a>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $keberatanStats['total']; ?></div>
                    <div class="stat-label">Total Keberatan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $keberatanStats['diajukan']; ?></div>
                    <div class="stat-label">Diajukan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $keberatanStats['diproses']; ?></div>
                    <div class="stat-label">Diproses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $keberatanStats['selesai']; ?></div>
                    <div class="stat-label">Selesai</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $keberatanStats['ditolak']; ?></div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>

            <div class="activity-list">
                <?php foreach ($keberatanRecent as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon <?php echo $activity['status_baru'] ?: 'diajukan'; ?>">
                            <?php 
                            $statusIcons = [
                                'diajukan' => '📝',
                                'diproses' => '⚙️',
                                'selesai' => '✅',
                                'ditolak' => '❌'
                            ];
                            echo $statusIcons[$activity['status_baru']] ?? '📝';
                            ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <?php echo htmlspecialchars($activity['nomor_registrasi']); ?> -
                                <?php echo htmlspecialchars($activity['nama_lengkap']); ?>
                            </div>
                            <div class="activity-time">
                                <?php 
                                $time = $activity['status_waktu'] ?: $activity['tanggal_pengajuan'];
                                echo date('d/m/Y H:i', strtotime($time)); 
                                ?> - 
                                Status: <?php echo htmlspecialchars($activity['status_baru'] ?: 'Diajukan'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>PPID Kabupaten Dompu</h3>
                    <p>Sistem Administrasi PPID</p>
                </div>
                <div class="footer-section">
                    <h4>Kontak</h4>
                    <p>Email: ppid@dompukab.go.id</p>
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





