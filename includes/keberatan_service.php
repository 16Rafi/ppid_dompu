<?php
require_once __DIR__ . '/config.php';

function generateKeberatanNumber() {
    global $conn;
    $date = date('Ymd');
    $prefix = "KBR-{$date}-";

    $stmt = $conn->prepare("SELECT nomor_registrasi FROM keberatan WHERE nomor_registrasi LIKE ? ORDER BY id DESC LIMIT 1");
    $likePattern = $prefix . '%';
    $stmt->bind_param("s", $likePattern);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $lastReg = $result->fetch_assoc()['nomor_registrasi'];
        $lastNumber = intval(substr($lastReg, -4));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    return $prefix . sprintf('%04d', $newNumber);
}

function validateKeberatanData($data) {
    $errors = [];

    $required = [
        'nama_lengkap',
        'identitas',
        'no_identitas',
        'scan_identitas',
        'informasi_diminta',
        'alasan_pengajuan'
    ];

    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Field " . str_replace('_', ' ', $field) . " wajib diisi";
        }
    }

    if (!empty($data['no_identitas']) && strlen(preg_replace('/\s+/', '', $data['no_identitas'])) < 5) {
        $errors[] = "Nomor identitas terlalu pendek";
    }

    return $errors;
}

function sanitizeKeberatanData($data) {
    return [
        'nama_lengkap' => htmlspecialchars(trim($data['nama_lengkap'])),
        'identitas' => htmlspecialchars(trim($data['identitas'])),
        'no_identitas' => htmlspecialchars(trim($data['no_identitas'])),
        'scan_identitas' => htmlspecialchars(trim($data['scan_identitas'])),
        'informasi_diminta' => htmlspecialchars(trim($data['informasi_diminta'])),
        'alasan_pengajuan' => htmlspecialchars(trim($data['alasan_pengajuan'])),
        'keterangan_tambahan' => htmlspecialchars(trim($data['keterangan_tambahan'] ?? ''))
    ];
}

function saveKeberatan($data) {
    global $conn;

    try {
        $conn->begin_transaction();

        $nomorRegistrasi = generateKeberatanNumber();

        $stmt = $conn->prepare("
            INSERT INTO keberatan 
            (nomor_registrasi, nama_lengkap, identitas, no_identitas, scan_identitas, informasi_diminta, alasan_pengajuan, keterangan_tambahan, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'diajukan')
        ");

        $stmt->bind_param(
            "ssssssss",
            $nomorRegistrasi,
            $data['nama_lengkap'],
            $data['identitas'],
            $data['no_identitas'],
            $data['scan_identitas'],
            $data['informasi_diminta'],
            $data['alasan_pengajuan'],
            $data['keterangan_tambahan']
        );

        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan keberatan: " . $stmt->error);
        }

        $keberatanId = $conn->insert_id;

        $stmtLog = $conn->prepare("
            INSERT INTO log_status_keberatan
            (keberatan_id, status_lama, status_baru, diubah_oleh)
            VALUES (?, NULL, 'diajukan', NULL)
        ");
        $stmtLog->bind_param("i", $keberatanId);
        if (!$stmtLog->execute()) {
            throw new Exception("Gagal menyimpan log status: " . $stmtLog->error);
        }

        $conn->commit();

        return [
            'success' => true,
            'keberatan_id' => $keberatanId,
            'nomor_registrasi' => $nomorRegistrasi
        ];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error saving keberatan: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function updateKeberatanStatus($keberatanId, $newStatus, $adminId = null) {
    global $conn;

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT status FROM keberatan WHERE id = ?");
        $stmt->bind_param("i", $keberatanId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Keberatan tidak ditemukan");
        }

        $currentStatus = $result->fetch_assoc()['status'];

        $stmtUpdate = $conn->prepare("UPDATE keberatan SET status = ? WHERE id = ?");
        $stmtUpdate->bind_param("si", $newStatus, $keberatanId);
        if (!$stmtUpdate->execute()) {
            throw new Exception("Gagal update status: " . $stmtUpdate->error);
        }

        $stmtLog = $conn->prepare("
            INSERT INTO log_status_keberatan 
            (keberatan_id, status_lama, status_baru, diubah_oleh) 
            VALUES (?, ?, ?, ?)
        ");
        $stmtLog->bind_param("issi", $keberatanId, $currentStatus, $newStatus, $adminId);
        if (!$stmtLog->execute()) {
            throw new Exception("Gagal menyimpan log status: " . $stmtLog->error);
        }

        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating keberatan status: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function getAllKeberatan($limit = 50, $offset = 0) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT id, nomor_registrasi, nama_lengkap, identitas, no_identitas,
               informasi_diminta, alasan_pengajuan, status, tanggal_pengajuan
        FROM keberatan
        ORDER BY tanggal_pengajuan DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getKeberatanDetail($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM keberatan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getKeberatanStatusHistory($keberatanId) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT status_lama, status_baru, waktu, diubah_oleh
        FROM log_status_keberatan
        WHERE keberatan_id = ?
        ORDER BY waktu ASC
    ");
    $stmt->bind_param("i", $keberatanId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
