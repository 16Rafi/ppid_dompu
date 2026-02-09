<?php
require_once '../includes/config.php';
require_once '../includes/permohonan_service.php';

// Set headers
header('Content-Type: text/html; charset=UTF-8');

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get public permohonan
$permohonanList = getPublicPermohonan($limit, $offset);

// Count total for pagination
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM permohonan_informasi WHERE status IN ('selesai', 'ditolak')");
$stmt->execute();
$totalResult = $stmt->get_result();
$totalPermohonan = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalPermohonan / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Permohonan Informasi - PPID Kabupaten Dompu</title>
    <meta name="description" content="Daftar permohonan informasi publik yang telah diproses PPID Kabupaten Dompu">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="daftar-permohonan-body">
    <?php include '../includes/header.php'; ?>

    <!-- Main Content -->
    <main class="daftar-page">
        <div class="page-header">
            <h1>Daftar Permohonan Informasi</h1>
            <p>Daftar permohonan informasi publik yang telah diproses oleh PPID Kabupaten Dompu sesuai dengan UU No. 14 Tahun 2008.</p>
        </div>

        <div class="info-box">
            <h4>Informasi Penting</h4>
            <p>Hanya permohonan dengan status <strong>Selesai</strong> atau <strong>Ditolak</strong> yang ditampilkan secara publik. Data pribadi (email, alamat, nomor HP) tidak ditampilkan untuk melindungi privasi pemohon.</p>
        </div>

        <div class="table-container">
            <?php if (!empty($permohonanList)): ?>
                <table class="permohonan-table">
                    <thead>
                        <tr>
                            <th>Nomor Registrasi</th>
                            <th>Tanggal</th>
                            <th>Nama Pemohon</th>
                            <th>Ringkasan Informasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permohonanList as $permohonan): ?>
                            <tr>
                                <td class="nomor-reg"><?php echo htmlspecialchars($permohonan['nomor_registrasi']); ?></td>
                                <td class="tanggal"><?php echo date('d/m/Y', strtotime($permohonan['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($permohonan['nama_pemohon']); ?></td>
                                <td class="ringkasan"><?php echo htmlspecialchars($permohonan['ringkasan_informasi']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $permohonan['status']; ?>">
                                        <?php 
                                        echo $permohonan['status'] === 'selesai' ? 'Selesai' : 'Ditolak'; 
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
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

            <?php else: ?>
                <div class="empty-state">
                    <h3>📋 Belum Ada Data</h3>
                    <p>Belum ada permohonan informasi yang selesai atau ditolak untuk ditampilkan.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>



