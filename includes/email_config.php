<?php
/**
 * Email Configuration for PPID Kabupaten Dompu
 * Supports both local development and production environments
 */

class EmailConfig {
    // SMTP Configuration
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_SECURE = 'tls'; // tls or ssl
    const SMTP_AUTH = true;
    
    // Gmail App Credentials (REPLACE with your actual credentials)
    const SMTP_USERNAME = 'rafiyudipramana@gmail.com'; // Your Gmail address
    const SMTP_PASSWORD = 'anld xwgr vtbu wcre';    // Gmail App Password (not regular password)
    
    // Email Settings
    const FROM_EMAIL = 'rafiyudipramana@gmail.com';   // Fixed From address
    const FROM_NAME = 'PPID Kabupaten Dompu';
    
    // Development/Production Switch
    const IS_DEVELOPMENT = true; // Set to false in production
    
    /**
     * Get SMTP configuration array
     */
    public static function getSMTPConfig() {
        return [
            'host' => self::SMTP_HOST,
            'port' => self::SMTP_PORT,
            'secure' => self::SMTP_SECURE,
            'auth' => self::SMTP_AUTH,
            'username' => self::SMTP_USERNAME,
            'password' => self::SMTP_PASSWORD
        ];
    }
    
    /**
     * Get recipient email based on environment
     */
    public static function getRecipientEmail() {
        if (self::IS_DEVELOPMENT) {
            return 'rafiyudipramana@gmail.com'; // Development email
        } else {
            return 'rafiyudipramana@gmail.com'; // Production email
        }
    }
    
    /**
     * Validate email configuration
     */
    public static function validateConfig() {
        $errors = [];
        
        // Check for placeholder/default values, not actual configured values
        if (empty(self::SMTP_USERNAME) || self::SMTP_USERNAME === 'your-email@gmail.com') {
            $errors[] = 'SMTP username not configured';
        }
        
        if (empty(self::SMTP_PASSWORD) || self::SMTP_PASSWORD === 'your-app-password') {
            $errors[] = 'SMTP password not configured';
        }
        
        return $errors;
    }
}
?>
