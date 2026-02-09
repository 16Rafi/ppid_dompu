<?php
/**
 * Email Logger for PPID System
 * Handles detailed logging of email sending attempts
 */

class EmailLogger {
    private $logFile;
    private $isDevelopment;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../logs/email.log';
        $this->isDevelopment = true; // Set based on your environment
        
        // Ensure logs directory exists
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log email sending attempt
     */
    public function logEmailAttempt($recipient, $subject, $formData = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'ATTEMPT',
            'recipient' => $recipient,
            'subject' => $subject,
            'form_data' => $formData,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $this->writeLog($logEntry);
    }
    
    /**
     * Log successful email sending
     */
    public function logEmailSuccess($recipient, $subject, $smtpInfo = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'SUCCESS',
            'recipient' => $recipient,
            'subject' => $subject,
            'smtp_info' => $smtpInfo,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $this->writeLog($logEntry);
    }
    
    /**
     * Log email sending failure
     */
    public function logEmailFailure($recipient, $subject, $errorInfo, $smtpDebug = '') {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'FAILURE',
            'recipient' => $recipient,
            'subject' => $subject,
            'error_info' => $errorInfo,
            'smtp_debug' => $smtpDebug,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $this->writeLog($logEntry);
    }
    
    /**
     * Write log entry to file
     */
    private function writeLog($entry) {
        $logLine = json_encode($entry) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get recent email logs
     */
    public function getRecentLogs($limit = 50) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = array_slice(array_reverse($lines), 0, $limit);
        
        return array_map(function($line) {
            return json_decode($line, true);
        }, $logs);
    }
    
    /**
     * Check if development mode
     */
    public function isDevelopment() {
        return $this->isDevelopment;
    }
}
?>
