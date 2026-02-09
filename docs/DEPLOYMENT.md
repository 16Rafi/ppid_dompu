# Backup dan Monitoring PPID Website

## Backup Database

### Backup Manual
```bash
php scripts/backup_database.php
```

### Backup Otomatis (Cron Job)
Tambahkan ke crontab untuk backup harian pukul 02:00:
```bash
0 2 * * * /usr/bin/php /path/to/ppid_dompu/scripts/backup_database.php
```

### Restore Database
```bash
php scripts/restore_database.php backups/backup_2024-01-15_02-00-00.sql
```

## Monitoring

### 1. Error Log
Lihat error log Apache:
```bash
tail -f /var/log/apache2/error.log
```

Untuk XAMPP Windows:
```
C:\xampp\apache\logs\error.log
```

### 2. Audit Log Admin
Query untuk melihat aktivitas admin hari ini:
```sql
SELECT * FROM admin_audit_log 
WHERE DATE(created_at) = CURDATE() 
ORDER BY created_at DESC;
```

### 3. Monitoring Disk Space
Cek ukuran backup folder:
```bash
du -sh backups/
```

### 4. Health Check Script
Buat script sederhana untuk cek kesehatan website:
```bash
curl -I https://domain-anda.com
```

## Security Headers

Website sudah dilengkapi dengan security headers:
- Content-Security-Policy (CSP)
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection
- Referrer-Policy
- Strict-Transport-Security (HSTS) saat HTTPS

## HTTPS Setup

### Untuk Production:
1. Install SSL certificate (Let's Encrypt recommended)
2. Update BASE_URL di config.php ke https
3. Pastikan virtual host mengarah ke port 443
4. Test redirect HTTP→HTTPS

### Contoh Virtual Host Apache:
```apache
<VirtualHost *:80>
    ServerName domain-anda.com
    Redirect permanent / https://domain-anda.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName domain-anda.com
    DocumentRoot /path/to/ppid_dompu
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
</VirtualHost>
```
