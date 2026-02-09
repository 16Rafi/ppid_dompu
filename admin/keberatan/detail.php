<?php
require_once '../../includes/config.php';
require_once '../../includes/keberatan_service.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

$keberatanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($keberatanId === 0) {
    echo '<p>ID keberatan tidak valid.</p>';
    exit;
}

$keberatan = getKeberatanDetail($keberatanId);
if (!$keberatan) {
    echo '<p>Keberatan tidak ditemukan.</p>';
    exit;
}

$statusHistory = getKeberatanStatusHistory($keberatanId);
?>

<div style="max-height: 500px; overflow-y: auto;">
    <div style="display: grid; gap: 1.5rem;">
        <div>
            <h3 style="color: #093A5A; margin-bottom: 1rem; border-bottom: 2px solid #F4B800; padding-bottom: 0.5rem;">
                📋 Informasi Pemohon
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A; width: 30%;">Nama Lengkap</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($keberatan['nama_lengkap']); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Identitas</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($keberatan['identitas']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">No Identitas</td>
                    <td style="padding: 0.5rem;"><?php echo htmlspecialchars($keberatan['no_identitas']); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Scan Identitas</td>
                    <td style="padding: 0.5rem;">
                        <?php if (!empty($keberatan['scan_identitas'])): ?>
                            <a href="<?php echo htmlspecialchars(buildUrl($keberatan['scan_identitas'])); ?>" target="_blank" rel="noopener noreferrer">Lihat File</a>
                        <?php else: ?>
                            <em>-</em>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <div>
            <h3 style="color: #093A5A; margin-bottom: 1rem; border-bottom: 2px solid #F4B800; padding-bottom: 0.5rem;">
                📝 Detail Keberatan
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A; width: 30%;">Nomor Registrasi</td>
                    <td style="padding: 0.5rem; font-family: 'Courier New', monospace; font-weight: 600; color: #093A5A;">
                        <?php echo htmlspecialchars($keberatan['nomor_registrasi']); ?>
                    </td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Tanggal Pengajuan</td>
                    <td style="padding: 0.5rem;"><?php echo date('d/m/Y H:i', strtotime($keberatan['tanggal_pengajuan'])); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Informasi Diminta</td>
                    <td style="padding: 0.5rem;"><?php echo nl2br(htmlspecialchars($keberatan['informasi_diminta'])); ?></td>
                </tr>
                <tr style="background: #f8f9fa;">
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Alasan Pengajuan</td>
                    <td style="padding: 0.5rem;"><?php echo nl2br(htmlspecialchars($keberatan['alasan_pengajuan'])); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 600; color: #093A5A;">Keterangan Tambahan</td>
                    <td style="padding: 0.5rem;"><?php echo nl2br(htmlspecialchars($keberatan['keterangan_tambahan'] ?: '-')); ?></td>
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
                            echo $statusColors[$keberatan['status']] ?? '';
                            ?>">
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
                </tr>
            </table>
        </div>

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
