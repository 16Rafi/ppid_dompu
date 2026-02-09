<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_ppid_dompu');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $cookieParams = session_get_cookie_params();
    
    // Hanya set cookie parameters jika headers belum dikirim
    if (!headers_sent()) {
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path' => $cookieParams['path'],
            'domain' => $cookieParams['domain'],
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    
    session_start();
}

// Regenerate session ID periodically (every 30 minutes)
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Rate limiting for login attempts
function checkLoginRateLimit($username) {
    $maxAttempts = 5;
    $lockoutTime = 15 * 60; // 15 minutes
    
    $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $attempts = &$_SESSION[$key];
    
    // Reset if lockout period has passed
    if (time() - $attempts['first_attempt'] > $lockoutTime) {
        $attempts = ['count' => 0, 'first_attempt' => time()];
    }
    
    $attempts['count']++;
    
    if ($attempts['count'] > $maxAttempts) {
        $remainingTime = $lockoutTime - (time() - $attempts['first_attempt']);
        return [
            'blocked' => true,
            'remaining_time' => $remainingTime,
            'message' => "Terlalu banyak percobaan login. Coba lagi dalam " . ceil($remainingTime / 60) . " menit."
        ];
    }
    
    return ['blocked' => false];
}

// Clear login attempts on successful login
function clearLoginAttempts($username) {
    $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
    unset($_SESSION[$key]);
}

// Audit log function
function logAdminAction($action, $targetType = null, $targetId = null, $details = null) {
    global $conn;
    
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    $adminId = (int)$_SESSION['admin_id'];
    $adminUsername = (string)($_SESSION['admin_username'] ?? 'Unknown');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO admin_audit_log (admin_id, admin_username, action, target_type, target_id, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssisss", $adminId, $adminUsername, $action, $targetType, $targetId, $details, $ipAddress, $userAgent);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
        return false;
    }
}

// Base URL
define('BASE_URL', 'http://localhost/ppid_dompu/');

// Site settings function
function getSetting($key) {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['setting_value'];
    }
    return '';
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Clean input function
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate slug
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

// Format date
function formatDate($date) {
    return date('d F Y', strtotime($date));
}

/**
 * Validate and move uploaded file with security checks.
 * Returns array ['success' => bool, 'filename' => string|null, 'error' => string|null]
 */
function validateAndMoveUpload(array $file, string $upload_dir): array {
    // Allowed extensions and MIME types
    $allowed = [
        'images' => [
            'ext' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'mime' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'max_size' => 5 * 1024 * 1024, // 5 MB
        ],
        'videos' => [
            'ext' => ['mp4'],
            'mime' => ['video/mp4'],
            'max_size' => 50 * 1024 * 1024, // 50 MB
        ],
    ];

    // Basic checks
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'filename' => null, 'error' => 'Invalid file upload.'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'filename' => null, 'error' => 'File terlalu besar (server limit).'];
        default:
            return ['success' => false, 'filename' => null, 'error' => 'Gagal upload file.'];
    }

    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return ['success' => false, 'filename' => null, 'error' => 'Gagal membuat folder upload.'];
        }
    }

    // Determine file type by MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        return ['success' => false, 'filename' => null, 'error' => 'Server tidak bisa membaca file info.'];
    }
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $type_key = null;
    foreach ($allowed as $key => $rules) {
        if (in_array($mime_type, $rules['mime'], true)) {
            $type_key = $key;
            break;
        }
    }
    if ($type_key === null) {
        return ['success' => false, 'filename' => null, 'error' => 'Tipe file tidak diizinkan.'];
    }

    // Check file size
    if ($file['size'] > $allowed[$type_key]['max_size']) {
        $max_mb = $allowed[$type_key]['max_size'] / (1024 * 1024);
        return ['success' => false, 'filename' => null, 'error' => "Ukuran file melebihi {$max_mb} MB."];
    }

    // Validate extension matches MIME
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed[$type_key]['ext'], true)) {
        return ['success' => false, 'filename' => null, 'error' => 'Ekstensi file tidak diizinkan.'];
    }

    // Generate safe filename (random)
    $new_name = bin2hex(random_bytes(16)) . '.' . $ext;
    $target_path = rtrim($upload_dir, '/') . '/' . $new_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => false, 'filename' => null, 'error' => 'Gagal menyimpan file.'];
    }

    return ['success' => true, 'filename' => $new_name, 'error' => null];
}

function buildUrl($url) {
    $url = trim((string)$url);
    if ($url === '' || $url === '#') {
        return '#';
    }
    if (preg_match('/^(https?:\/\/|\/\/|mailto:|tel:)/i', $url)) {
        return $url;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
}

function buildImageUrl($path) {
    $path = trim((string)$path);
    if ($path === '') {
        return '';
    }
    if (preg_match('/^(https?:\/\/|\/\/)/i', $path)) {
        return $path;
    }
    if (str_starts_with($path, 'uploads/')) {
        return rtrim(BASE_URL, '/') . '/' . $path;
    }
    if (str_contains($path, '/')) {
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
    return rtrim(BASE_URL, '/') . '/uploads/' . $path;
}

/**
 * Sanitize HTML content for news articles using HTMLPurifier.
 * Whitelists safe tags/attributes for news content.
 */
function sanitizeHtmlContent(string $html): string {
    if (!class_exists('HTMLPurifier')) {
        // Fallback: strip tags + basic security
        $allowed = '<p><br><strong><em><b><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><table><tr><td><th><thead><tbody><tfoot><caption>';
        $html = strip_tags($html, $allowed);
        
        // Remove event handlers and dangerous attributes
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/', '', $html);
        $html = preg_replace('/\s*javascript\s*:/i', '', $html);
        $html = preg_replace('/\s*vbscript\s*:/i', '', $html);
        $html = preg_replace('/\s*data\s*:/i', '', $html);
        
        return $html;
    }

    $config = HTMLPurifier_Config::createDefault();
    
    // Allowed HTML elements for news content
    $config->set('HTML.Allowed', 'p,br,strong,em,b,i,u,ul,ol,li,a[href|title],h1,h2,h3,h4,h5,h6,blockquote,table,tr,td,th,thead,tbody,tfoot,caption');
    
    // Allowed attributes for specific elements
    $config->set('HTML.AllowedAttributes', 'a.href,a.title');
    
    // Force rel="noopener noreferrer" on external links for security
    $config->set('HTML.DefinitionID', 'news-content');
    $config->set('HTML.DefinitionRev', 1);
    $def = $config->getHTMLDefinition(true);
    $def->addAttribute('a', 'target', 'Enum#_blank,_self,_parent,_top');
    $def->addAttribute('a', 'rel', 'Text');
    
    // Auto format and tidy
    $config->set('AutoFormat.RemoveEmpty', true);
    $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
    
    // Security settings
    $config->set('URI.DisableExternalResources', true); // Disable external resources like images
    $config->set('URI.DisableResources', true);
    $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
    
    // Filter to add rel="noopener noreferrer" to external links
    $config->set('Filter.Custom', [
        new class extends HTMLPurifier_Filter {
            public $name = 'AddNoOpener';
            
            public function postFilter($html, $config, $context) {
                $html = preg_replace_callback(
                    '/<a\s+([^>]*href=["\']?(?!https?:\/\/)([^"\']*)["\']?[^>]*)>/i',
                    function ($matches) {
                        $attrs = $matches[1];
                        // Skip if it's an internal link or anchor
                        if (preg_match('/^#/', $matches[2]) || !preg_match('/^https?:\/\//', $matches[2])) {
                            return $matches[0];
                        }
                        // Add rel and target for external links
                        if (strpos($attrs, 'rel=') === false) {
                            $attrs .= ' rel="noopener noreferrer"';
                        }
                        if (strpos($attrs, 'target=') === false) {
                            $attrs .= ' target="_blank"';
                        }
                        return "<a $attrs>";
                    },
                    $html
                );
                return $html;
            }
        }
    ]);
    
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($html);
}
?>
