<?php
// Fix path untuk config.php agar bisa diakses dari berbagai lokasi
$configPath = __DIR__ . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    // Fallback untuk backward compatibility
    require_once 'config.php';
}

/**
 * Generate unique registration number for information requests
 * Format: PPID-YYYYMMDD-XXXX
 */
function generateRegistrationNumber() {
    global $conn;
    
    $date = date('Ymd');
    $prefix = "PPID-{$date}-";
    
    // Get the last registration number for today
    $stmt = $conn->prepare("SELECT nomor_registrasi FROM permohonan_informasi WHERE nomor_registrasi LIKE ? ORDER BY id DESC LIMIT 1");
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

/**
 * Save information request to database
 */
function savePermohonanInformasi($data) {
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Generate registration number
        $nomorRegistrasi = generateRegistrationNumber();
        
        // Insert into permohonan_informasi
        $stmt = $conn->prepare("
            INSERT INTO permohonan_informasi 
            (nomor_registrasi, nama_pemohon, email, no_hp, alamat, pekerjaan, jenis_identitas, nomor_identitas, scan_identitas, tujuan_perangkat, informasi_diminta, tujuan_penggunaan, cara_mendapatkan, cara_pengambilan, cara_memperoleh, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'diajukan')
        ");
        
        $stmt->bind_param(
            "sssssssssssssss",
            $nomorRegistrasi,
            $data['nama_pemohon'],
            $data['email'],
            $data['no_hp'],
            $data['alamat'],
            $data['pekerjaan'],
            $data['jenis_identitas'],
            $data['nomor_identitas'],
            $data['scan_identitas'],
            $data['tujuan_perangkat'],
            $data['informasi_diminta'],
            $data['tujuan_penggunaan'],
            $data['cara_mendapatkan'],
            $data['cara_pengambilan'],
            $data['cara_memperoleh']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan permohonan: " . $stmt->error);
        }
        
        $permohonanId = $conn->insert_id;
        
        // Insert into log_status_permohonan
        $stmtLog = $conn->prepare("
            INSERT INTO log_status_permohonan 
            (permohonan_id, status_lama, status_baru, diubah_oleh) 
            VALUES (?, NULL, 'diajukan', NULL)
        ");
        
        $stmtLog->bind_param("i", $permohonanId);
        
        if (!$stmtLog->execute()) {
            throw new Exception("Gagal menyimpan log status: " . $stmtLog->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true,
            'permohonan_id' => $permohonanId,
            'nomor_registrasi' => $nomorRegistrasi
        ];
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        error_log("Error saving permohonan: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Update permohonan status
 */
function updatePermohonanStatus($permohonanId, $newStatus, $adminId = null) {
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get current status
        $stmt = $conn->prepare("SELECT status FROM permohonan_informasi WHERE id = ?");
        $stmt->bind_param("i", $permohonanId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Permohonan tidak ditemukan");
        }
        
        $currentStatus = $result->fetch_assoc()['status'];
        
        // Update status
        $stmtUpdate = $conn->prepare("UPDATE permohonan_informasi SET status = ? WHERE id = ?");
        $stmtUpdate->bind_param("si", $newStatus, $permohonanId);
        
        if (!$stmtUpdate->execute()) {
            throw new Exception("Gagal update status: " . $stmtUpdate->error);
        }
        
        // Insert log
        $stmtLog = $conn->prepare("
            INSERT INTO log_status_permohonan 
            (permohonan_id, status_lama, status_baru, diubah_oleh) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmtLog->bind_param("issi", $permohonanId, $currentStatus, $newStatus, $adminId);
        
        if (!$stmtLog->execute()) {
            throw new Exception("Gagal menyimpan log status: " . $stmtLog->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        return ['success' => true];
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        error_log("Error updating status: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get all permohonan for admin
 */
function getAllPermohonan($limit = 50, $offset = 0) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT id, nomor_registrasi, nama_pemohon, email, no_hp, alamat, 
               informasi_diminta, tujuan_penggunaan, cara_memperoleh, status, created_at
        FROM permohonan_informasi 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get public permohonan (only completed/rejected)
 */
function getPublicPermohonan($limit = 50, $offset = 0) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT id, nomor_registrasi, nama_pemohon, 
               LEFT(informasi_diminta, 100) as ringkasan_informasi,
               status, created_at
        FROM permohonan_informasi 
        WHERE status IN ('selesai', 'ditolak')
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get permohonan detail by ID
 */
function getPermohonanDetail($id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT * FROM permohonan_informasi WHERE id = ?
    ");
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get status history for a permohonan
 */
function getPermohonanStatusHistory($permohonanId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT status_lama, status_baru, waktu, diubah_oleh
        FROM log_status_permohonan 
        WHERE permohonan_id = ?
        ORDER BY waktu ASC
    ");
    
    $stmt->bind_param("i", $permohonanId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Validate and sanitize input data
 */
function validatePermohonanData($data) {
    $errors = [];
    
    // Required fields
    $required = [
        'nama_pemohon',
        'email',
        'alamat',
        'pekerjaan',
        'jenis_identitas',
        'nomor_identitas',
        'scan_identitas',
        'tujuan_perangkat',
        'informasi_diminta',
        'tujuan_penggunaan',
        'cara_mendapatkan',
        'cara_pengambilan',
        'cara_memperoleh'
    ];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Field " . str_replace('_', ' ', $field) . " wajib diisi";
        }
    }
    
    // Email validation
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Phone validation (optional)
    if (!empty($data['no_hp'])) {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $data['no_hp']);
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            $errors[] = "Nomor HP tidak valid (minimal 10 digit, maksimal 15 digit)";
        }
    }
    
    // Text length validation
    if (!empty($data['informasi_diminta']) && strlen($data['informasi_diminta']) > 1000) {
        $errors[] = "Informasi yang diminta terlalu panjang (maksimal 1000 karakter)";
    }
    
    if (!empty($data['tujuan_penggunaan']) && strlen($data['tujuan_penggunaan']) > 500) {
        $errors[] = "Tujuan penggunaan terlalu panjang (maksimal 500 karakter)";
    }
    
    return $errors;
}

/**
 * Sanitize input data
 */
function sanitizePermohonanData($data) {
    return [
        'nama_pemohon' => htmlspecialchars(trim($data['nama_pemohon'])),
        'email' => filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL),
        'no_hp' => htmlspecialchars(trim($data['no_hp'] ?? '')),
        'alamat' => htmlspecialchars(trim($data['alamat'])),
        'pekerjaan' => htmlspecialchars(trim($data['pekerjaan'] ?? '')),
        'jenis_identitas' => htmlspecialchars(trim($data['jenis_identitas'] ?? '')),
        'nomor_identitas' => htmlspecialchars(trim($data['nomor_identitas'] ?? '')),
        'scan_identitas' => htmlspecialchars(trim($data['scan_identitas'] ?? '')),
        'tujuan_perangkat' => htmlspecialchars(trim($data['tujuan_perangkat'] ?? '')),
        'informasi_diminta' => htmlspecialchars(trim($data['informasi_diminta'])),
        'tujuan_penggunaan' => htmlspecialchars(trim($data['tujuan_penggunaan'])),
        'cara_mendapatkan' => htmlspecialchars(trim($data['cara_mendapatkan'] ?? '')),
        'cara_pengambilan' => htmlspecialchars(trim($data['cara_pengambilan'] ?? '')),
        'cara_memperoleh' => htmlspecialchars(trim($data['cara_memperoleh']))
    ];
}
?>
