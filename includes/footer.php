<?php
if (!isset($conn)) {
    require_once __DIR__ . '/config.php';
}
?>
<!-- Footer -->
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
                <p>Telepon: 085239602826</p>
            </div>
            <div class="footer-section">
                <h4>Alamat</h4>
                <p>Gedung Paruga Parenta Lantai III<br>Jl. Beringin Nomor 01<br>Kecamatan Dompu<br>Kabupaten Dompu</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> PPID Kabupaten Dompu. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="<?php echo buildUrl('js/script.js'); ?>"></script>
