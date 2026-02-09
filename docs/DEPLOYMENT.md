# Deployment & Operasional PPID Website

Dokumen ini melengkapi README dengan fokus pada deployment produksi, konfigurasi penting, dan operasional harian.

## 1) Persyaratan Server Produksi
- PHP 8.1+ (disarankan 8.2)
- MySQL/MariaDB
- Apache/Nginx
- Ekstensi PHP: `mysqli`, `mbstring`, `fileinfo`, `openssl`
- Composer

## 2) Konfigurasi Wajib

### BASE_URL
Edit `includes/config.php`:
```php
define('BASE_URL', 'https://ppid.domain.go.id/');
```

### PHP Production Settings
- `display_errors = Off`
- `log_errors = On`

Jika `display_errors` aktif, **CSV export akan tercemar warning PHP**.

### Folder Upload
Pastikan folder `uploads/` writable oleh user web server.

Linux:
```bash
chown -R www-data:www-data /path/to/ppid_dompu/uploads
chmod -R 755 /path/to/ppid_dompu/uploads
```

## 3) Database Produksi

### Import awal
- Buat database (default: `db_ppid_dompu`)
- Import file `database/database.sql`

### Migrasi penting (jika database lama)
- Tabel keberatan lama perlu di-rename dan dibuat ulang.
- Kolom tambahan `permohonan_informasi` harus ada:
  - pekerjaan
  - jenis_identitas
  - nomor_identitas
  - scan_identitas
  - tujuan_perangkat
  - cara_mendapatkan
  - cara_pengambilan

### Verifikasi charset
Gunakan `utf8mb4` untuk semua tabel.

## 4) Web Server

### Apache (contoh)
```apache
<VirtualHost *:80>
    ServerName ppid.domain.go.id
    Redirect permanent / https://ppid.domain.go.id/
</VirtualHost>

<VirtualHost *:443>
    ServerName ppid.domain.go.id
    DocumentRoot /var/www/ppid_dompu

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/ppid.crt
    SSLCertificateKeyFile /etc/ssl/private/ppid.key

    <Directory /var/www/ppid_dompu>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx (contoh)
```nginx
server {
    listen 80;
    server_name ppid.domain.go.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name ppid.domain.go.id;

    root /var/www/ppid_dompu;
    index index.php;

    ssl_certificate /etc/ssl/certs/ppid.crt;
    ssl_certificate_key /etc/ssl/private/ppid.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## 5) Keamanan
- CSP dan security headers otomatis via `includes/security_headers.php`
- Pastikan HTTPS aktif agar HSTS berjalan.
- Pastikan `uploads/` tidak mengizinkan eksekusi PHP (konfigurasi web server).

## 6) Backup & Monitoring

### Backup Database
```bash
php scripts/backup_database.php
```

### Backup Otomatis
```bash
0 2 * * * /usr/bin/php /path/to/ppid_dompu/scripts/backup_database.php
```

### Restore
```bash
php scripts/restore_database.php backups/backup_YYYY-MM-DD_HH-MM-SS.sql
```

### Monitoring
- Apache error log: `/var/log/apache2/error.log`
- XAMPP Windows: `C:\xampp\apache\logs\error.log`
- Audit log:
```sql
SELECT * FROM admin_audit_log ORDER BY created_at DESC LIMIT 100;
```

## 7) Checklist Go-Live
- [ ] BASE_URL sudah di-set ke domain produksi
- [ ] Database import + migrasi selesai
- [ ] Email SMTP konfigurasi sudah diuji
- [ ] uploads/ writable
- [ ] display_errors Off
- [ ] HTTPS aktif
- [ ] Menu navigasi diperiksa
- [ ] Export CSV diuji
