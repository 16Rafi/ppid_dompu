<?php
require_once 'includes/config.php';
require_once 'includes/security_headers.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo buildUrl('img/Kabupaten Dompu.png'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getSetting('site_title') ?: 'PPID Kabupaten Dompu'; ?></title>
    <meta name="description" content="<?php echo getSetting('site_description'); ?>">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"> 
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg">
            <img src="img/110202185043.jpg" alt="Gedung Kominfo Kabupaten Dompu">
        </div>
        <div class="hero-content">
            <div class="container">
                <h1><?php echo getSetting('hero_title') ?: 'Informasi Mengenai PPID Kabupaten Dompu'; ?></h1>
                <p><?php echo getSetting('hero_description'); ?></p>
            </div>
        </div>
    </section>

    <!-- Pusat Pelayanan Section -->
    <section class="pusat-pelayanan-section">
        <div class="container">
            <div class="pelayanan-header">
                <h2 class="section-title">Pusat Pelayanan</h2>
            </div>
            
            <div class="pelayanan-time">
                <div class="time-box">
                    <i class="time-icon">🕐</i>
                    <span>Jam Pelayanan 08:00 s/d 16:30 WITA (Senin - Kamis) & 08:00 s/d 10:30 WITA (Jum'at)</span>
                </div>
            </div>

            <div class="pelayanan-main">
                <a href="pages/permohonan-informasi.php" class="pelayanan-box large">
                    <div class="box-icon">
                        <img src="img/form.png" alt="Formulir" class="icon-placeholder">
                    </div>
                    <div class="box-content">
                        <h3>Ajukan Permohonan Informasi</h3>
                    </div>
                </a>

                <a href="pages/pengajuan-keberatan.php" class="pelayanan-box large">
                    <div class="box-icon">
                        <img src="img/form.png" alt="Formulir" class="icon-placeholder">
                    </div>
                    <div class="box-content">
                        <h3>Pengajuan Keberatan</h3>
                    </div>
                </a>
                
                <a href="https://aduannomor.id/" class="pelayanan-box large" target="_blank" rel="noopener noreferrer">
                    <div class="box-icon">
                        <img src="img/form.png" alt="Formulir" class="icon-placeholder">
                    </div>
                    <div class="box-content">
                        <h3>Pengaduan Nomor</h3>
                    </div>
                </a>
                
                <a href="https://aduankonten.id/" class="pelayanan-box large" target="_blank" rel="noopener noreferrer">
                    <div class="box-icon">
                        <img src="img/form.png" alt="Formulir" class="icon-placeholder">
                    </div>
                    <div class="box-content">
                        <h3>Pengaduan Konten</h3>
                    </div>
                </a>
            </div>

            <div class="pelayanan-sub">
                <a href="pages/dip.php" class="pelayanan-box small">
                    <h4>Daftar Informasi Publik PPID</h4>
                </a>

                <a href="pages/laporan-permohonan.php" class="pelayanan-box small">
                    <h4>Laporan Permohonan Informasi Publik</h4>
                </a>

                <a href="pages/laporan-keberatan.php" class="pelayanan-box small">
                    <h4>Laporan Pelayanan Keberatan Informasi Publik</h4>
                </a>
            </div>
        </div>
    </section>

    <!-- DIP Terbaru Section -->
    <section class="dip-terbaru-section">
        <div class="container">
            <div class="dip-terbaru-header">
                <h2 class="section-title">DIP Terbaru</h2>
                <a href="pages/dip.php" class="dip-terbaru-link">Lihat Semua</a>
            </div>
            <div class="dip-terbaru-table">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th>Lampiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("
                            SELECT dip.*, f.path
                            FROM daftar_informasi_publik dip
                            LEFT JOIN files f ON dip.file_id = f.id
                            WHERE dip.status_publikasi = 'published'
                            ORDER BY dip.created_at DESC
                            LIMIT 5
                        ");
                        $stmt->execute();
                        $dipResult = $stmt->get_result();
                        if ($dipResult && $dipResult->num_rows > 0) {
                            while ($dip = $dipResult->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($dip['judul']) . '</td>';
                                echo '<td>' . htmlspecialchars($dip['kategori']) . '</td>';
                                echo '<td>' . htmlspecialchars($dip['tahun'] ?? '-') . '</td>';
                                echo '<td>';
                                if (!empty($dip['path'])) {
                                    echo '<a href="' . htmlspecialchars(buildUrl($dip['path'])) . '" target="_blank" rel="noopener noreferrer">Unduh</a>';
                                } else {
                                    echo '<em>-</em>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4" style="text-align:center; padding: 1rem;">Belum ada data DIP.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section class="news-section">
        <div class="container">
            <div class="news-header">
                <h2>Berita Dompu</h2>
                <a href="pages/berita.php" class="news-all-link">Lihat Semua</a>
            </div>
            <div class="news-divider"></div>
            <div class="news-list">
                <?php
                $query = "SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 4";
                $result = $conn->query($query);
                
                if ($result && $result->num_rows > 0) {
                    while ($news = $result->fetch_assoc()) {
                        echo '<div class="news-item">';
                        echo '<div class="news-img">';
                        if (!empty($news['image'])) {
                            echo '<img src="uploads/' . $news['image'] . '" alt="' . htmlspecialchars($news['title'] ?? 'Berita') . '">';
                        } else {
                            echo '<img src="https://via.placeholder.com/140x90/7392A8/FFFFFF?text=Berita" alt="' . htmlspecialchars($news['title'] ?? 'Berita') . '">';
                        }
                        echo '</div>';
                        echo '<div class="news-content">';
                        echo '<div class="news-meta">';
                        echo '<span class="news-category">Berita</span>';
                        echo '<span class="news-date">' . formatDate($news['created_at'] ?? date('Y-m-d')) . '</span>';
                        echo '</div>';
                        echo '<a href="pages/berita-detail.php?slug=' . ($news['slug'] ?? '#') . '" class="news-title-link">';
                        echo '<h3 class="news-title">' . htmlspecialchars($news['title'] ?? 'Judul Berita') . '</h3>';
                        echo '</a>';
                        echo '<div class="news-excerpt">' . htmlspecialchars($news['excerpt'] ?? 'Excerpt berita tidak tersedia.') . '</div>';
                        echo '<a href="pages/berita-detail.php?slug=' . ($news['slug'] ?? '#') . '" class="news-more">Selengkapnya</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div style="text-align: center; padding: 40px 20px; background: #f8f9fa; border-radius: 8px;">';
                    echo '<p style="color: #666;">Belum ada berita yang dipublish.</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Satu Data Dompu Section -->
    <section class="satudata-dompu-section">
        <div class="satudata-dompu-header">
            <img src="img/satu-data.png" alt="Satu Data Dompu">
            <h2>Satu Data Dompu</h2>
        </div>

        <div class="satudata-dompu-slider">
            <div class="satudata-dompu-slides">
                <div class="satudata-dompu-slide">
                    <a class="satudata-dompu-card" href="https://satudata.dompukab.go.id/">
                        <div class="satudata-dompu-card-icon">🏙️</div>
                        <div class="satudata-dompu-card-title">Kabupaten Dompu Dalam Angka 2024</div>
                    </a>

                    <a class="satudata-dompu-card" href="https://satudata.dompukab.go.id/">
                        <div class="satudata-dompu-card-icon">🏥</div>
                        <div class="satudata-dompu-card-title">Profil Kesehatan Kabupaten Dompu 2023</div>
                    </a>

                    <a class="satudata-dompu-card" href="https://satudata.dompukab.go.id/">
                        <div class="satudata-dompu-card-icon">🌾</div>
                        <div class="satudata-dompu-card-title">Publikasi Perikanan Tangkap 2023</div>
                    </a>
                </div>

                <div class="satudata-dompu-slide">
                    <a class="satudata-dompu-card" href="https://satudata.dompukab.go.id/">
                        <div class="satudata-dompu-card-icon">📊</div>
                        <div class="satudata-dompu-card-title">Kecamatan Dompu Dalam Angka 2024</div>
                    </a>

                    <a class="satudata-dompu-card" href="https://satudata.dompukab.go.id/">
                        <div class="satudata-dompu-card-icon">📈</div>
                        <div class="satudata-dompu-card-title">Kecamatan Woja Dalam Angka 2024</div>
                    </a>

                    <a class="satudata-dompu-card" href="https://satudata.dompukab.go.id/">
                        <div class="satudata-dompu-card-icon">📘</div>
                        <div class="satudata-dompu-card-title">Kecamatan Kilo Dalam Angka 2024</div>
                    </a>
                </div>
            </div>

        </div>

        <div class="satudata-dompu-footer">
            <a href="https://satudata.dompukab.go.id/">Lihat Semua Dataset</a>
        </div>
    </section>

    <!-- External Websites Section -->
    <section class="external-websites">
        <div class="container">
            <h2 class="section-title">Website Perangkat Daerah</h2>
            <div class="websites-carousel">
                <div class="carousel-wrapper">
                    <button class="carousel-arrow prev" id="prevBtn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="carousel-container">
                        <div class="websites-track">
                            <?php
                            $query = "SELECT * FROM external_websites WHERE is_active = 1 ORDER BY order_index";
                            $result = $conn->query($query);
                            
                            while ($link = $result->fetch_assoc()) {
                                echo '<div class="website-card carousel-item">';
                                if (!empty($link['image'])) {
                                    echo '<div class="card-image">';
                                    echo '<img src="' . htmlspecialchars($link['image']) . '" alt="' . htmlspecialchars($link['name']) . '" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                                    echo '<div class="icon-placeholder" style="display:none;">';
                                    echo '<svg width="40" height="40" viewBox="0 0 24 24" fill="none">';
                                    echo '<path d="M10 6V8H5V19H16V14H18V20C18 20.5523 17.5523 21 17 21H4C3.44772 21 3 20.5523 3 20V7C3 6.44772 3.44772 6 4 6H10Z" fill="currentColor"/>';
                                    echo '<path d="M14 6V8H19V13H21V6C21 5.44772 20.5523 5 20 5H13V7H14Z" fill="currentColor"/>';
                                    echo '</svg>';
                                    echo '</div>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="card-image">';
                                    echo '<div class="icon-placeholder">';
                                    echo '<svg width="40" height="40" viewBox="0 0 24 24" fill="none">';
                                    echo '<path d="M10 6V8H5V19H16V14H18V20C18 20.5523 17.5523 21 17 21H4C3.44772 21 3 20.5523 3 20V7C3 6.44772 3.44772 6 4 6H10Z" fill="currentColor"/>';
                                    echo '<path d="M14 6V8H19V13H21V6C21 5.44772 20.5523 5 20 5H13V7H14Z" fill="currentColor"/>';
                                    echo '</svg>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                echo '<div class="card-content">';
                                echo '<h3>' . htmlspecialchars($link['name']) . '</h3>';
                                echo '<p>' . htmlspecialchars($link['description']) . '</p>';
                                echo '<div class="card-buttons">';
                                echo '<a href="' . htmlspecialchars($link['url']) . '" class="btn btn-primary" target="_blank">Kunjungi</a>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <button class="carousel-arrow next" id="nextBtn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>



