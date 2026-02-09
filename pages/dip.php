<?php
require_once '../includes/config.php';
require_once '../includes/security_headers.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stats = [
    'total' => 0,
    'berkala' => 0,
    'serta-merta' => 0,
    'setiap-saat' => 0
];

$stmt = $conn->prepare("SELECT kategori, COUNT(*) as count FROM daftar_informasi_publik WHERE status_publikasi = 'published' GROUP BY kategori");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $stats[$row['kategori']] = (int)$row['count'];
    $stats['total'] += (int)$row['count'];
}

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM daftar_informasi_publik WHERE status_publikasi = 'published'");
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRows = (int)$totalResult->fetch_assoc()['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

$stmt = $conn->prepare("
    SELECT dip.*, f.nama_file, f.path
    FROM daftar_informasi_publik dip
    LEFT JOIN files f ON dip.file_id = f.id
    WHERE dip.status_publikasi = 'published'
    ORDER BY dip.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$dipList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIP - PPID Kabupaten Dompu</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 </head>
<body class="dip-page">
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

    <main class="dip-content">
        <div class="container">
            <div class="page-header">
                <h1>Daftar Informasi Publik (DIP)</h1>
                <p>Informasi publik yang tersedia di PPID Kabupaten Dompu.</p>
            </div>

            <div class="dip-stats">
                <div class="dip-stat-card">
                    <div class="dip-stat-number"><?php echo $stats['total']; ?></div>
                    <div class="dip-stat-label">Total Informasi</div>
                </div>
                <div class="dip-stat-card">
                    <div class="dip-stat-number"><?php echo $stats['berkala']; ?></div>
                    <div class="dip-stat-label">Berkala</div>
                </div>
                <div class="dip-stat-card">
                    <div class="dip-stat-number"><?php echo $stats['serta-merta']; ?></div>
                    <div class="dip-stat-label">Serta Merta</div>
                </div>
                <div class="dip-stat-card">
                    <div class="dip-stat-number"><?php echo $stats['setiap-saat']; ?></div>
                    <div class="dip-stat-label">Setiap Saat</div>
                </div>
            </div>

            <div class="table-container">
                <table class="dip-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Ringkasan</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th>Lampiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dipList)): ?>
                            <?php $no = ($page - 1) * $limit + 1; ?>
                            <?php foreach ($dipList as $dip): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($dip['judul']); ?></td>
                                    <td><?php echo htmlspecialchars($dip['ringkasan'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($dip['kategori']); ?></td>
                                    <td><?php echo htmlspecialchars($dip['tahun'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (!empty($dip['path'])): ?>
                                            <a href="<?php echo htmlspecialchars(buildUrl($dip['path'])); ?>" target="_blank" rel="noopener noreferrer">Unduh</a>
                                        <?php else: ?>
                                            <em>-</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 1.5rem;">Belum ada data DIP.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
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
