<?php
/**
 * Email Service for PPID System
 * Handles email sending with robust success/failure detection
 */

require_once 'email_config.php';
require_once 'email_logger.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $logger;
    private $config;
    private $isDevelopment;
    
    public function __construct() {
        $this->logger = new EmailLogger();
        $this->config = EmailConfig::getSMTPConfig();
        $this->isDevelopment = EmailConfig::IS_DEVELOPMENT;
    }
    
    /**
     * Send email with comprehensive error handling
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $htmlBody HTML email body
     * @param string $textBody Plain text email body
     * @param array $attachments Array of file paths to attach
     * @param array $replyTo Reply-to information ['email' => '', 'name' => '']
     * @return array Result with status, message, and technical details
     */
    public function sendEmail($to, $subject, $htmlBody, $textBody = '', $attachments = [], $replyTo = []) {
        $sendStatus = [
            'success' => false,
            'message' => '',
            'technical_details' => [],
            'smtp_debug' => ''
        ];
        
        try {
            // Log email attempt
            $this->logger->logEmailAttempt($to, $subject, [
                'has_attachments' => !empty($attachments),
                'attachment_count' => count($attachments),
                'has_reply_to' => !empty($replyTo)
            ]);
            
            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            
            // Enable SMTP debug in development mode
            if ($this->isDevelopment) {
                $mail->SMTPDebug = SMTP::DEBUG_CONNECTION; // Show connection details
                $mail->Debugoutput = function($str, $level) use (&$sendStatus) {
                    $sendStatus['smtp_debug'] .= $str . "\n";
                };
            }
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = $this->config['auth'];
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = $this->config['secure'];
            $mail->Port = $this->config['port'];
            
            // Connection timeout settings
            $mail->Timeout = 30; // 30 seconds timeout
            $mail->SMTPKeepAlive = true;
            
            // Recipients
            $mail->setFrom(EmailConfig::FROM_EMAIL, EmailConfig::FROM_NAME);
            $mail->addAddress($to);
            
            // Add Reply-To if provided
            if (!empty($replyTo['email'])) {
                $mail->addReplyTo($replyTo['email'], $replyTo['name'] ?? '');
            }
            
            // Add attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    $mail->addAttachment(
                        $attachment['path'], 
                        $attachment['name'] ?? basename($attachment['path'])
                    );
                }
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?: strip_tags($htmlBody);
            
            // Additional headers
            $mail->XMailer = 'PPID Kabupaten Dompu Mailer';
            $mail->CharSet = 'UTF-8';
            
            // Attempt to send email
            $sendResult = $mail->send();
            
            if ($sendResult) {
                // Email sent successfully
                $sendStatus['success'] = true;
                $sendStatus['message'] = 'Email berhasil dikirim ke server SMTP';
                $sendStatus['technical_details'] = [
                    'smtp_host' => $this->config['host'],
                    'smtp_port' => $this->config['port'],
                    'recipient' => $to,
                    'subject' => $subject,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'message_id' => $mail->getLastMessageID() ?? 'unknown'
                ];
                
                // Log success
                $this->logger->logEmailSuccess($to, $subject, $sendStatus['technical_details']);
                
            } else {
                // This should not happen with exceptions enabled, but handle it
                throw new Exception('Email sending returned false without exception');
            }
            
        } catch (Exception $e) {
            // Email sending failed
            $errorMessage = $mail->ErrorInfo ?: $e->getMessage();
            
            $sendStatus['success'] = false;
            $sendStatus['message'] = 'Gagal mengirim email';
            $sendStatus['technical_details'] = [
                'error_type' => get_class($e),
                'error_message' => $errorMessage,
                'smtp_host' => $this->config['host'],
                'smtp_port' => $this->config['port'],
                'recipient' => $to,
                'subject' => $subject,
                'timestamp' => date('Y-m-d H:i:s'),
                'phpmailer_error' => $mail->ErrorInfo
            ];
            
            // Log failure
            $this->logger->logEmailFailure($to, $subject, $errorMessage, $sendStatus['smtp_debug']);
        }
        
        return $sendStatus;
    }
    
    /**
     * Test SMTP connection
     */
    public function testSMTPConnection() {
        $testResult = [
            'success' => false,
            'message' => '',
            'details' => []
        ];
        
        try {
            $mail = new PHPMailer(true);
            
            // Enable debug for testing
            $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
            
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = $this->config['auth'];
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = $this->config['secure'];
            $mail->Port = $this->config['port'];
            
            // Test connection
            if ($mail->smtpConnect()) {
                $testResult['success'] = true;
                $testResult['message'] = 'Koneksi SMTP berhasil';
                $testResult['details'] = [
                    'host' => $this->config['host'],
                    'port' => $this->config['port'],
                    'secure' => $this->config['secure'],
                    'auth' => $this->config['auth'] ? 'enabled' : 'disabled'
                ];
                $mail->smtpClose();
            } else {
                throw new Exception('Failed to connect to SMTP server');
            }
            
        } catch (Exception $e) {
            $testResult['success'] = false;
            $testResult['message'] = 'Koneksi SMTP gagal';
            $testResult['details'] = [
                'error' => $e->getMessage(),
                'host' => $this->config['host'],
                'port' => $this->config['port']
            ];
        }
        
        return $testResult;
    }
    
    /**
     * Get email sending statistics
     */
    public function getEmailStats() {
        $logs = $this->logger->getRecentLogs(100);
        
        $stats = [
            'total_attempts' => 0,
            'successful_sends' => 0,
            'failed_sends' => 0,
            'success_rate' => 0,
            'recent_failures' => []
        ];
        
        foreach ($logs as $log) {
            if ($log['type'] === 'ATTEMPT') {
                $stats['total_attempts']++;
            } elseif ($log['type'] === 'SUCCESS') {
                $stats['successful_sends']++;
            } elseif ($log['type'] === 'FAILURE') {
                $stats['failed_sends']++;
                if (count($stats['recent_failures']) < 5) {
                    $stats['recent_failures'][] = [
                        'timestamp' => $log['timestamp'],
                        'error' => $log['error_info'] ?? 'Unknown error'
                    ];
                }
            }
        }
        
        if ($stats['total_attempts'] > 0) {
            $stats['success_rate'] = round(($stats['successful_sends'] / $stats['total_attempts']) * 100, 2);
        }
        
        return $stats;
    }
}
?>
