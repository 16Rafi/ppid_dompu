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
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIP - PPID Kabupaten Dompu</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 </head>
<body class="dip-page">
    <?php include '../includes/header.php'; ?>

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

    <?php include '../includes/footer.php'; ?>
</body>
</html>



