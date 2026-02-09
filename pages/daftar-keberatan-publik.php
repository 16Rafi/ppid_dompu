<?php
require_once '../includes/config.php';
require_once '../includes/security_headers.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM keberatan
    WHERE status IN ('selesai', 'ditolak')
");
$stmt->execute();
$totalResult = $stmt->get_result();
$totalKeberatan = (int)$totalResult->fetch_assoc()['total'];
$totalPages = max(1, (int)ceil($totalKeberatan / $limit));

$stmt = $conn->prepare("
    SELECT nomor_registrasi, nama_lengkap, informasi_diminta, status, tanggal_pengajuan
    FROM keberatan
    WHERE status IN ('selesai', 'ditolak')
    ORDER BY tanggal_pengajuan DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$keberatanList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Keberatan Publik - PPID Kabupaten Dompu</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="daftar-permohonan-body">
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h2>PPID</h2>
                </div>
                <nav class="nav-menu">
                    <ul class="nav-list">
                        <?php
                        $query = "SELECT * FROM menus WHERE parent_id = 0 AND is_active = 1 ORDER BY order_index";
                        $result = $conn->query($query);

                        while ($menu = $result->fetch_assoc()) {
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

    <main class="daftar-page">
        <div class="container">
            <div class="page-header">
                <h1>Daftar Keberatan Publik</h1>
                <p>Hanya keberatan dengan status <strong>Selesai</strong> atau <strong>Ditolak</strong> yang ditampilkan.</p>
            </div>

            <div class="table-container">
                <table class="permohonan-table">
                    <thead>
                        <tr>
                            <th>No. Registrasi</th>
                            <th>Tanggal</th>
                            <th>Nama Pemohon</th>
                            <th>Informasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($keberatanList)): ?>
                            <?php foreach ($keberatanList as $k): ?>
                                <tr>
                                    <td class="nomor-reg"><?php echo htmlspecialchars($k['nomor_registrasi']); ?></td>
                                    <td class="tanggal"><?php echo date('d/m/Y', strtotime($k['tanggal_pengajuan'])); ?></td>
                                    <td><?php echo htmlspecialchars($k['nama_lengkap']); ?></td>
                                    <td class="ringkasan"><?php echo htmlspecialchars($k['informasi_diminta']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $k['status']; ?>">
                                            <?php echo $k['status'] === 'selesai' ? 'Selesai' : 'Ditolak'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 1.5rem;">Belum ada keberatan selesai/ditolak.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                    <?php endif; ?>
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

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
