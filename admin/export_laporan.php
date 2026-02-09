<?php
require_once '../includes/config.php';
require_once '../includes/permohonan_service.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_permohonan_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV Headers
$headers = [
    'Nomor Registrasi',
    'Tanggal Permohonan',
    'Nama Pemohon',
    'Email',
    'Nomor HP',
    'Alamat',
    'Informasi Diminta',
    'Tujuan Penggunaan',
    'Cara Memperoleh',
    'Status',
    'Tanggal Update'
];

fputcsv($output, $headers);

// Get all permohonan data
$stmt = $conn->prepare("
    SELECT * FROM permohonan_informasi 
    ORDER BY created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

// Write data rows
while ($row = $result->fetch_assoc()) {
    $updatedAt = $row['updated_at'] ?? null;
    $csvRow = [
        $row['nomor_registrasi'],
        date('d/m/Y H:i', strtotime($row['created_at'])),
        $row['nama_pemohon'],
        $row['email'],
        $row['no_hp'] ?: '',
        $row['alamat'],
        $row['informasi_diminta'],
        $row['tujuan_penggunaan'],
        $row['cara_memperoleh'],
        $row['status'],
        $updatedAt ? date('d/m/Y H:i', strtotime($updatedAt)) : ''
    ];
    
    fputcsv($output, $csvRow);
}

// Close output stream
fclose($output);

// Log the export action
logAdminAction('EXPORT_LAPORAN', 'permohonan_informasi', null, 'Export CSV laporan permohonan');
?>
