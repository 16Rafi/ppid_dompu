<?php
/**
 * File Upload Handler for PPID Form
 * Secure file upload with validation
 */

class FileUploadHandler {
    private $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
    private $maxFileSize = 2 * 1024 * 1024; // 2MB
    private $uploadDir;
    
    public function __construct() {
        $this->uploadDir = __DIR__ . '/../uploads/';
        
        // Ensure upload directory exists
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Handle file upload with security validation
     */
    public function handleUpload($fileInputName, $fieldName = 'file') {
        if (!isset($_FILES[$fileInputName])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        $file = $_FILES[$fileInputName];
        
        // Check upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (maksimal 2MB)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder tidak ditemukan',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                UPLOAD_ERR_EXTENSION => 'File upload dihentikan oleh extension'
            ];
            
            return [
                'success' => false, 
                'error' => $errorMessages[$file['error']] ?? 'Unknown upload error'
            ];
        }
        
        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'error' => 'File terlalu besar (maksimal 2MB)'];
        }
        
        // Validate file type
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $this->allowedTypes)) {
            return [
                'success' => false, 
                'error' => 'Tipe file tidak diizinkan. Hanya: ' . implode(', ', $this->allowedTypes)
            ];
        }
        
        // Validate MIME type (additional security)
        $allowedMimes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'error' => 'Tipe file tidak valid'];
        }
        
        // Generate secure filename
        $filename = $this->generateSecureFilename($fileInfo['filename'], $extension);
        $uploadPath = $this->uploadDir . $filename;
        
        // Move file to upload directory
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $uploadPath,
                'original_name' => $file['name']
            ];
        } else {
            return ['success' => false, 'error' => 'Gagal mengupload file'];
        }
    }
    
    /**
     * Generate secure filename to prevent directory traversal
     */
    private function generateSecureFilename($originalName, $extension) {
        // Remove special characters and spaces
        $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
        // Add timestamp and random string for uniqueness
        $timestamp = date('Y-m-d_H-i-s');
        $random = bin2hex(random_bytes(4));
        
        return sprintf('%s_%s_%s.%s', $cleanName, $timestamp, $random, $extension);
    }
    
    /**
     * Get file URL for display
     */
    public function getFileUrl($filename) {
        return '/uploads/' . $filename;
    }
    
    /**
     * Clean up old files (optional maintenance)
     */
    public function cleanupOldFiles($daysOld = 30) {
        $files = glob($this->uploadDir . '*');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > ($daysOld * 24 * 60 * 60)) {
                unlink($file);
            }
        }
    }
}
?>
