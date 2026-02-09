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
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Keberatan Publik - PPID Kabupaten Dompu</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="daftar-permohonan-body">
    <?php include '../includes/header.php'; ?>

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

    <?php include '../includes/footer.php'; ?>
</body>
</html>



