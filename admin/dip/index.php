<?php
require_once '../../includes/config.php';
require_once '../../includes/security_headers.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$stats = [
    'total' => 0,
    'berkala' => 0,
    'serta-merta' => 0,
    'setiap-saat' => 0
];

$stmt = $conn->prepare("SELECT kategori, COUNT(*) as count FROM daftar_informasi_publik GROUP BY kategori");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $stats[$row['kategori']] = (int)$row['count'];
    $stats['total'] += (int)$row['count'];
}

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM daftar_informasi_publik");
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRows = (int)$totalResult->fetch_assoc()['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

$stmt = $conn->prepare("
    SELECT dip.*, f.nama_file, f.path
    FROM daftar_informasi_publik dip
    LEFT JOIN files f ON dip.file_id = f.id
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
    <title>Kelola DIP - Admin PPID</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body admin-dip">
   <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h2><a href="../dashboard.php" style="color: #FCFDFD;">PPID Admin</a></h2>
                </div>
                <nav class="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item"><a href="../dashboard.php" class="nav-link">Dashboard</a></li>
                        <li class="nav-item"><a href="../permohonan/index.php" class="nav-link">Permohonan</a></li>
                        <li class="nav-item"><a href="../keberatan/index.php" class="nav-link">Keberatan</a></li>
                        <li class="nav-item"><a href="index.php" class="nav-link active">DIP</a></li>
                        <li class="nav-item"><a href="../pages/index.php" class="nav-link">Pages</a></li>
                        <li class="nav-item"><a href="../menus/index.php" class="nav-link">Menu</a></li>
                        <li class="nav-item"><a href="../laporan.php" class="nav-link">Laporan</a></li>
                        <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
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
            <h1 class="page-title">Kelola Daftar Informasi Publik (DIP)</h1>
            <div class="admin-info">
                <span>Admin: <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Informasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['berkala']; ?></div>
                <div class="stat-label">Berkala</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['serta-merta']; ?></div>
                <div class="stat-label">Serta Merta</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['setiap-saat']; ?></div>
                <div class="stat-label">Setiap Saat</div>
            </div>
        </div>

        <div class="section-card">
            <h2 class="section-title">Input Data DIP</h2>
            <form method="POST" action="save.php" enctype="multipart/form-data" id="dipForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="dip_id" id="dipId" value="">
                <input type="hidden" name="existing_file_id" id="existingFileId" value="">

                <div class="form-group">
                    <label for="judul">Judul Informasi *</label>
                    <input type="text" id="judul" name="judul" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="ringkasan">Ringkasan</label>
                    <textarea id="ringkasan" name="ringkasan" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kategori">Kategori *</label>
                        <select id="kategori" name="kategori" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="berkala">Berkala</option>
                            <option value="serta-merta">Serta Merta</option>
                            <option value="setiap-saat">Setiap Saat</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tahun">Tahun</label>
                        <input type="number" id="tahun" name="tahun" class="form-control" min="2000" max="2100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status_publikasi">Status Publikasi</label>
                        <select id="status_publikasi" name="status_publikasi" class="form-control">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="lampiran">Lampiran (PDF/JPG/PNG)</label>
                        <input type="file" id="lampiran" name="lampiran" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="currentFileInfo" class="help-text"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan</button>
                    <button type="button" class="btn-secondary" id="dipResetBtn">Batal</button>
                </div>
            </form>
        </div>

        <div class="section-card">
            <h2 class="section-title">Daftar DIP</h2>
            <div class="table-container">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th>Status</th>
                            <th>Lampiran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dipList)): ?>
                            <?php foreach ($dipList as $dip): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dip['judul']); ?></td>
                                    <td><?php echo htmlspecialchars($dip['kategori']); ?></td>
                                    <td><?php echo htmlspecialchars($dip['tahun'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($dip['status_publikasi']); ?></td>
                                    <td>
                                        <?php if (!empty($dip['path'])): ?>
                                            <a href="<?php echo htmlspecialchars(buildUrl($dip['path'])); ?>" target="_blank" rel="noopener noreferrer">Lihat</a>
                                        <?php else: ?>
                                            <em>-</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button
                                                type="button"
                                                class="btn-small btn-detail js-edit-dip"
                                                data-id="<?php echo (int)$dip['id']; ?>"
                                                data-judul="<?php echo htmlspecialchars($dip['judul'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-ringkasan="<?php echo htmlspecialchars($dip['ringkasan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-kategori="<?php echo htmlspecialchars($dip['kategori'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-tahun="<?php echo htmlspecialchars($dip['tahun'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-status="<?php echo htmlspecialchars($dip['status_publikasi'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-file-id="<?php echo htmlspecialchars($dip['file_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-file-name="<?php echo htmlspecialchars($dip['nama_file'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            >Edit</button>
                                            <form method="POST" action="save.php" onsubmit="return confirm('Hapus data ini?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="dip_id" value="<?php echo (int)$dip['id']; ?>">
                                                <button type="submit" class="btn-small btn-status">Hapus</button>
                                            </form>
                                        </div>
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

    <script src="../../js/script.js"></script>
</body>
</html>
