<?php
/**
 * Security Headers for PPID Website
 * Add this file to the top of your main entry points (index.php, admin/index.php, etc.)
 */

// HTTPS redirect (only in production)
function enforceHTTPS() {
    // Check if we're in production (not localhost)
    $isProduction = !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '0.0.0.0']);
    
    if ($isProduction && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
        $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirectUrl, true, 301);
        exit();
    }
}

// Set security headers
function setSecurityHeaders() {
    // Content Security Policy - Prevent XSS
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
           "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.gstatic.com; " .
           "font-src 'self' https://fonts.gstatic.com; " .
           "img-src 'self' data: https:; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none'; " .
           "base-uri 'self'; " .
           "form-action 'self';";
    
    header("Content-Security-Policy: " . $csp);
    
    // Prevent clickjacking
    header("X-Frame-Options: DENY");
    
    // Prevent MIME type sniffing
    header("X-Content-Type-Options: nosniff");
    
    // Enable XSS protection (legacy browsers)
    header("X-XSS-Protection: 1; mode=block");
    
    // Referrer policy
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Permissions policy (optional)
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    
    // HSTS (only in production with HTTPS)
    $isProduction = !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '0.0.0.0']);
    if ($isProduction && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    }
}

// Apply security measures
enforceHTTPS();
setSecurityHeaders();
?>
