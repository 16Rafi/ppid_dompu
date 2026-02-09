<?php
require_once '../../includes/config.php';
require_once '../../includes/keberatan_service.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$keberatanList = getAllKeberatan($limit, $offset);

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM keberatan");
$stmt->execute();
$totalResult = $stmt->get_result();
$totalKeberatan = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalKeberatan / $limit);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $keberatanId = (int)$_POST['keberatan_id'];
    $newStatus = $_POST['status'];

    $updateResult = updateKeberatanStatus($keberatanId, $newStatus, $_SESSION['admin_id']);
    if ($updateResult['success']) {
        logAdminAction('UPDATE_KEBERATAN_STATUS', 'keberatan', $keberatanId, "Update status to: $newStatus");
        $successMessage = "Status keberatan berhasil diperbarui";
    } else {
        $errorMessage = "Gagal memperbarui status: " . $updateResult['error'];
    }

    header("Location: index.php?page=$page" . (isset($successMessage) ? "&success=" . urlencode($successMessage) : ""));
    exit;
}

$successMessage = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$errorMessage = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Keberatan - Admin PPID</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body admin-keberatan">
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
                            <a href="index.php" class="nav-link active">Keberatan</a>
                        </li>
                        <li class="nav-item">
                            <a href="../dip/index.php" class="nav-link">DIP</a>
                        </li>
                        <li class="nav-item">
                            <a href="../pages/index.php" class="nav-link">Pages</a>
                        </li>
                        <li class="nav-item">
                            <a href="../menus/index.php" class="nav-link">Menu</a>
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

    <main class="admin-page">
        <div class="page-header">
            <h1 class="page-title">Daftar Pengajuan Keberatan</h1>
            <div class="admin-info">
                <span>Admin: <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            </div>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <?php
            $stats = [
                'total' => $totalKeberatan,
                'diajukan' => 0,
                'diproses' => 0,
                'selesai' => 0,
                'ditolak' => 0
            ];
            $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM keberatan GROUP BY status");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $stats[$row['status']] = $row['count'];
            }
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Keberatan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['diajukan']; ?></div>
                <div class="stat-label">Diajukan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['diproses']; ?></div>
                <div class="stat-label">Diproses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['selesai']; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['ditolak']; ?></div>
                <div class="stat-label">Ditolak</div>
            </div>
        </div>

        <div class="section-card">
            <h2 class="section-title">📋 Daftar Keberatan</h2>
            <div class="table-container">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>No. Registrasi</th>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            <th>Identitas</th>
                            <th>Informasi Diminta</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($keberatanList)): ?>
                            <?php foreach ($keberatanList as $keberatan): ?>
                                <tr>
                                    <td class="nomor-reg"><?php echo htmlspecialchars($keberatan['nomor_registrasi']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($keberatan['tanggal_pengajuan'])); ?></td>
                                    <td><?php echo htmlspecialchars($keberatan['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($keberatan['identitas']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($keberatan['informasi_diminta'], 0, 50)) . '...'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $keberatan['status']; ?>">
                                            <?php
                                            $statusLabels = [
                                                'diajukan' => 'Diajukan',
                                                'diproses' => 'Diproses',
                                                'selesai' => 'Selesai',
                                                'ditolak' => 'Ditolak'
                                            ];
                                            echo $statusLabels[$keberatan['status']] ?? $keberatan['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="btn-small btn-detail js-detail-keberatan" data-id="<?php echo (int)$keberatan['id']; ?>">
                                                Detail
                                            </button>
                                            <button type="button" class="btn-small btn-status js-status-keberatan" data-id="<?php echo (int)$keberatan['id']; ?>" data-status="<?php echo htmlspecialchars($keberatan['status'], ENT_QUOTES, 'UTF-8'); ?>">
                                                Ubah Status
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #7392A8;">
                                    <h3>📋 Belum Ada Data</h3>
                                    <p>Belum ada pengajuan keberatan yang masuk.</p>
                                </td>
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

    <div id="keberatanStatusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeKeberatanStatusModal()">&times;</span>
            <h2 style="color: #093A5A; margin-bottom: 1.5rem;">Ubah Status Keberatan</h2>

            <form method="POST" id="keberatanStatusForm">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="keberatan_id" id="keberatanId">

                <div class="form-group">
                    <label for="keberatanCurrentStatus">Status Saat Ini:</label>
                    <input type="text" id="keberatanCurrentStatus" readonly style="background: #f8f9fa; color: #666;">
                </div>

                <div class="form-group">
                    <label for="keberatanStatus">Status Baru:</label>
                    <select name="status" id="keberatanStatus" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="diajukan">Diajukan</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeKeberatanStatusModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <div id="keberatanDetailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeKeberatanDetailModal()">&times;</span>
            <h2 style="color: #093A5A; margin-bottom: 1.5rem;">Detail Keberatan</h2>
            <div id="keberatanDetailContent"></div>
        </div>
    </div>

    <script src='../../js/script.js'></script>
</body>
</html>
