<?php
/**
 * Database Backup Script for PPID Website
 * Run this script periodically (daily/weekly) for automated backups
 */

// Configuration
$dbHost = DB_HOST;
$dbName = DB_NAME;
$dbUser = DB_USER;
$dbPass = DB_PASS;
$backupDir = __DIR__ . '/../backups/';

// Create backup directory if it doesn't exist
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Generate filename with timestamp
$filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$filepath = $backupDir . $filename;

// Create backup using mysqldump
$command = sprintf(
    'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s 2>&1',
    escapeshellarg($dbHost),
    escapeshellarg($dbUser),
    escapeshellarg($dbPass),
    escapeshellarg($dbName),
    escapeshellarg($filepath)
);

exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "Backup berhasil: $filename\n";
    
    // Keep only last 7 backups
    $files = glob($backupDir . 'backup_*.sql');
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    if (count($files) > 7) {
        $toDelete = array_slice($files, 7);
        foreach ($toDelete as $file) {
            unlink($file);
        }
    }
} else {
    echo "Backup gagal: " . implode("\n", $output) . "\n";
}
?>
