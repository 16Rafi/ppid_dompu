<?php
require_once '../includes/config.php';
require_once '../includes/keberatan_service.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_keberatan_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

$headers = [
    'Nomor Registrasi',
    'Tanggal Pengajuan',
    'Nama Lengkap',
    'Identitas',
    'No Identitas',
    'Scan Identitas',
    'Informasi Diminta',
    'Alasan Pengajuan',
    'Keterangan Tambahan',
    'Status',
    'Tanggal Putusan'
];

fputcsv($output, $headers);

$stmt = $conn->prepare("
    SELECT *
    FROM keberatan
    ORDER BY tanggal_pengajuan DESC
");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $csvRow = [
        $row['nomor_registrasi'],
        date('d/m/Y H:i', strtotime($row['tanggal_pengajuan'])),
        $row['nama_lengkap'],
        $row['identitas'],
        $row['no_identitas'],
        $row['scan_identitas'],
        $row['informasi_diminta'],
        $row['alasan_pengajuan'],
        $row['keterangan_tambahan'] ?: '',
        $row['status'],
        $row['tanggal_putusan'] ? date('d/m/Y H:i', strtotime($row['tanggal_putusan'])) : ''
    ];

    fputcsv($output, $csvRow);
}

fclose($output);

logAdminAction('EXPORT_LAPORAN', 'keberatan', null, 'Export CSV laporan keberatan');
?>
