<?php
require_once '../../includes/config.php';
require_once '../../includes/permohonan_service.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

// Get permohonan ID
$permohonanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($permohonanId === 0) {
    echo '<p>ID permohonan tidak valid.</p>';
    exit;
}

// Get permohonan detail
$permohonan = getPermohonanDetail($permohonanId);

if (!$permohonan) {
    echo '<p>Permohonan tidak ditemukan.</p>';
    exit;
}

// Get status history
$statusHistory = getPermohonanStatusHistory($permohonanId);
?>

<div style="max-height: 500px; overflow-y: auto;">
    <div style="display: grid; gap: 1.5rem;">
        <!-- Informasi Pemohon -->
        <div>
            <h3 style="color: #093A5A; margin-bottom: 1rem; border-bottom: 2px solid #F4B800; padding-bottom: 0.5rem;">
                📋 Informasi Pemohon
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A; width: 30%;">Nama Lengkap</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($permohonan['nama_pemohon']); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Email</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($permohonan['email']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Nomor HP</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($permohonan['no_hp'] ?: '-'); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Alamat</td>
                    <td style="padding: 0.5rem;"><?php echo nl2br(htmlspecialchars($permohonan['alamat'])); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Pekerjaan</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($permohonan['pekerjaan'] ?? '-'); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Jenis Identitas</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($permohonan['jenis_identitas'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Nomor Identitas</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($permohonan['nomor_identitas'] ?? '-'); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Scan Identitas</td>
                    <td style="padding: 0.5rem;">
                        <?php if (!empty($permohonan['scan_identitas'])): ?>
                            <a href="<?php echo htmlspecialchars(buildUrl($permohonan['scan_identitas'])); ?>" target="_blank" rel="noopener noreferrer">Lihat File</a>
                        <?php else: ?>
                            <em>-</em>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Informasi Permohonan -->
        <div>
            <h3 style="color: #093A5A; margin-bottom: 1rem; border-bottom: 2px solid #F4B800; padding-bottom: 0.5rem;">
                📄 Detail Permohonan
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A; width: 30%;">Nomor Registrasi</td>
                    <td style="padding: 0.5rem; font-family: 'Courier New', monospace; font-weight: 600; color: #093A5A;">
                        <?php echo htmlspecialchars($permohonan['nomor_registrasi']); ?>
                    </td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Tanggal Permohonan</td>
                    <td style="padding: 0.5rem;"><?php echo date('d/m/Y H:i', strtotime($permohonan['created_at'])); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Informasi Diminta</td>
                    <td style="padding: 0.5rem;"><?php echo nl2br(htmlspecialchars($permohonan['informasi_diminta'])); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Perangkat Daerah Tujuan</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($permohonan['tujuan_perangkat'] ?? '-'); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Tujuan Penggunaan</td>
                    <td style="padding: 0.5rem;"><?php echo nl2br(htmlspecialchars($permohonan['tujuan_penggunaan'])); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Cara Mendapatkan Informasi</td>
                    <td style="padding: 0.5rem;"><?php echo nl2br(htmlspecialchars($permohonan['cara_mendapatkan'] ?? '-')); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Cara Memperoleh</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($permohonan['cara_memperoleh']); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Cara Pengambilan</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($permohonan['cara_pengambilan'] ?? '-'); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Status Saat Ini</td>
                    <td style="padding: 0.5rem;">
                        <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;
                            <?php
                            $statusColors = [
                                'diajukan' => 'background: #fff3cd; color: #856404; border: 1px solid #ffeaa7;',
                                'diproses' => 'background: #cce5ff; color: #004085; border: 1px solid #99ccff;',
                                'selesai' => 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;',
                                'ditolak' => 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'
                            ];
                            echo $statusColors[$permohonan['status']] ?? '';
                            ?>">
                            <?php 
                            $statusLabels = [
                                'diajukan' => 'Diajukan',
                                'diproses' => 'Diproses', 
                                'selesai' => 'Selesai',
                                'ditolak' => 'Ditolak'
                            ];
                            echo $statusLabels[$permohonan['status']] ?? $permohonan['status'];
                            ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Riwayat Status -->
        <div>
            <h3 style="color: #093A5A; margin-bottom: 1rem; border-bottom: 2px solid #F4B800; padding-bottom: 0.5rem;">
                📊 Riwayat Status
            </h3>
            <?php if (!empty($statusHistory)): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #093A5A; color: white;">
                            <th style="padding: 0.75rem; text-align: left;">Waktu</th>
                            <th style="padding: 0.75rem; text-align: left;">Status Lama</th>
                            <th style="padding: 0.75rem; text-align: left;">Status Baru</th>
                            <th style="padding: 0.75rem; text-align: left;">Diubah Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statusHistory as $history): ?>
                            <tr style="<?php echo $history['status_lama'] === null ? 'background: #e7f3ff;' : ''; ?>">
                                <td style="padding: 0.5rem;"><?php echo date('d/m/Y H:i', strtotime($history['waktu'])); ?></td>
                                <td style="padding: 0.5rem;">
                                    <?php echo $history['status_lama'] ? htmlspecialchars($history['status_lama']) : '<em style="color: #666;">Baru</em>'; ?>
                                </td>
                                <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">
                                    <?php echo htmlspecialchars($history['status_baru']); ?>
                                </td>
                                <td style="padding: 0.5rem;">
                                    <?php echo $history['diubah_oleh'] ? 'Admin #' . $history['diubah_oleh'] : '<em style="color: #666;">System</em>'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666; font-style: italic;">Belum ada riwayat perubahan status.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
