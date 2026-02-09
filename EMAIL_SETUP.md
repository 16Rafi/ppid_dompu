# PPID Email System Setup Instructions

## 📋 Prerequisites
- PHP 7.4+
- Composer installed
- Gmail account with App Password enabled
- XAMPP/WAMP/LAMP stack

## 🚀 Installation Steps

### 1. Install PHPMailer (Already Done)
```bash
composer require phpmailer/phpmailer
```

### 2. Configure Gmail SMTP

#### Step 2.1: Enable 2-Factor Authentication
1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable 2-Step Verification
3. Go to App Passwords: https://myaccount.google.com/apppasswords

#### Step 2.2: Generate App Password
1. Select "Mail" for app
2. Select "Other (Custom name)" for device
3. Name it: "PPID Website"
4. Copy the 16-character password (without spaces)

#### Step 2.3: Update Email Configuration
Edit `includes/email_config.php`:

```php
const SMTP_USERNAME = 'your-gmail@gmail.com';     // Your Gmail
const SMTP_PASSWORD = 'your-16-char-app-password'; // App password
const FROM_EMAIL = 'noreply@dompukab.go.id';       // Your domain email
```

### 3. Production Configuration

#### For Production Server:
```php
const IS_DEVELOPMENT = false; // Change to false
```

#### Update Production Email:
```php
const SMTP_USERNAME = 'production-email@gmail.com';
const SMTP_PASSWORD = 'production-app-password';
```

#### Update Recipient:
```php
const getRecipientEmail() {
    if (self::IS_DEVELOPMENT) {
        return 'dev@example.com'; // Development
    } else {
        return 'ppid@dompukab.go.id'; // Production
    }
}
```

## 🧪 Testing Options

### Option 1: Gmail SMTP (Recommended for Testing)
- Use your personal Gmail with App Password
- Test form submission
- Check email inbox

### Option 2: MailHog (Local Testing)
Install MailHog for local development:

```bash
# Download MailHog
wget https://github.com/mailhog/MailHog/releases/download/v1.0.1/MailHog_linux_amd64
chmod +x MailHog_linux_amd64
./MailHog_linux_amd64

# Access at: http://localhost:8025
```

Update config for MailHog:
```php
const SMTP_HOST = 'localhost';
const SMTP_PORT = 1025;
const SMTP_SECURE = ''; // No encryption
```

## 🔧 File Structure
```
ppid_dompu/
├── includes/
│   ├── email_config.php      # SMTP configuration
│   ├── file_upload.php       # File upload handler
│   └── config.php           # Database config
├── vendor/                  # Composer dependencies
├── uploads/                 # Uploaded files
└── pages/
    └── permohonan-informasi.php  # Form with PHPMailer
```

## 📧 Email Features

### ✅ What's Included:
- **Professional HTML Email Template** with styling
- **File Attachment Support** (PDF, JPG, PNG max 2MB)
- **Secure File Upload** with validation
- **SMTP Authentication** via Gmail
- **Reply-To Functionality** (user's email)
- **Registration Number** generation
- **Error Handling** with logging
- **Development/Production** modes

### 📋 Email Template Features:
- Responsive design
- Professional header/footer
- Data tables for clarity
- File attachment indicator
- Registration number
- PPID branding

## 🔒 Security Features

### File Upload Security:
- **MIME Type Validation** (prevents malicious files)
- **File Size Limit** (2MB max)
- **File Type Restriction** (PDF, JPG, PNG only)
- **Secure Filename Generation** (prevents directory traversal)
- **Upload Directory Protection**

### Email Security:
- **SMTP Authentication** (no open relay)
- **Input Sanitization** (XSS prevention)
- **Error Logging** (debugging without exposing details)
- **Reply-To Headers** (proper email flow)

## 🚨 Troubleshooting

### Common Issues:

#### 1. "SMTP Error: Could not authenticate"
- Check Gmail App Password (16 characters, no spaces)
- Ensure 2FA is enabled on Gmail account
- Verify SMTP username and password

#### 2. "File upload failed"
- Check uploads directory permissions (755)
- Verify file size < 2MB
- Ensure file type is PDF/JPG/PNG

#### 3. "Email not received"
- Check spam/junk folder
- Verify recipient email address
- Check SMTP logs in error_log

#### 4. "Connection timed out"
- Check firewall blocking port 587
- Verify internet connection
- Try different SMTP port (465 with SSL)

## 📞 Support

For issues:
1. Check PHP error logs: `tail -f /var/log/php_errors.log`
2. Enable debug mode in PHPMailer: `$mail->SMTPDebug = 3;`
3. Verify all configurations are correct

## 🔄 Maintenance

### Regular Tasks:
- Monitor upload directory size
- Clean old files (automatic cleanup available)
- Update Gmail App Password if compromised
- Monitor email delivery rates

### Backup Strategy:
- Backup `uploads/` directory regularly
- Backup email configuration
- Monitor error logs

---

**Ready to use!** The system is now configured for professional email delivery with PHPMailer + SMTP.
