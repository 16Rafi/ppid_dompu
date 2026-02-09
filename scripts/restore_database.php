<?php
/**
 * Database Restore Script for PPID Website
 * Usage: php restore_database.php backup_file.sql
 */

if ($argc < 2) {
    echo "Usage: php restore_database.php <backup_file.sql>\n";
    exit(1);
}

$backupFile = $argv[1];

if (!file_exists($backupFile)) {
    echo "Error: Backup file tidak ditemukan: $backupFile\n";
    exit(1);
}

// Configuration
$dbHost = DB_HOST;
$dbName = DB_NAME;
$dbUser = DB_USER;
$dbPass = DB_PASS;

echo "Memulai restore database dari: $backupFile\n";
echo "Target database: $dbName\n";
echo "Apakah Anda yakin? (y/N): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
    echo "Restore dibatalkan.\n";
    exit(0);
}

// Restore using mysql command
$command = sprintf(
    'mysql --host=%s --user=%s --password=%s %s < %s 2>&1',
    escapeshellarg($dbHost),
    escapeshellarg($dbUser),
    escapeshellarg($dbPass),
    escapeshellarg($dbName),
    escapeshellarg($backupFile)
);

exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "Database berhasil di restore dari: $backupFile\n";
} else {
    echo "Restore gagal: " . implode("\n", $output) . "\n";
}
?>
