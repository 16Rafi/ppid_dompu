# Email Setup (PPID Website)

Dokumen ini menjelaskan konfigurasi email untuk notifikasi **permohonan informasi** dan **keberatan**.

## 1) Prasyarat
- PHP 8.x
- Composer
- PHPMailer (sudah terpasang via `composer install`)
- Akun SMTP (Gmail atau server mail instansi)

## 2) Konfigurasi Utama
Edit `includes/email_config.php`:

```php
const SMTP_HOST = 'smtp.example.go.id';
const SMTP_PORT = 587;
const SMTP_USERNAME = 'ppid@example.go.id';
const SMTP_PASSWORD = 'password';
const SMTP_SECURE = 'tls';
const FROM_EMAIL = 'ppid@example.go.id';
const FROM_NAME = 'PPID Kabupaten Dompu';
```

### Recipient (Email Tujuan)
Fungsi `getRecipientEmail()` menentukan email penerima notifikasi.
Pastikan di **production** diarahkan ke email resmi instansi.

## 3) Mode Development vs Production
```php
const IS_DEVELOPMENT = false;
```
- Jika `true`, log SMTP detail akan aktif.
- Gunakan `false` di production.

## 4) Reply-To
Permohonan informasi mengatur **Reply-To** ke email pemohon.
Keberatan tidak memakai reply-to (bisa ditambahkan jika diperlukan).

## 5) Pengujian
1. Submit form permohonan (`pages/permohonan-informasi.php`).
2. Submit form keberatan (`pages/pengajuan-keberatan.php`).
3. Pastikan email masuk ke inbox penerima.

## 6) SMTP Non-Gmail (Instansi)
Jika instansi memakai server mail internal:
- Pastikan port dan enkripsi sesuai (`tls`/`ssl`).
- Jika perlu whitelist IP server.

## 7) Log Email
Log pengiriman email ada di `logs/` melalui `includes/email_logger.php`.
Pastikan folder log dapat ditulis.

## 8) Troubleshooting Singkat
- **SMTP Error: Could not authenticate**
  - username/password salah
  - port/enkripsi salah
- **Email tidak terkirim**
  - cek firewall outbound port 587/465
  - cek server SMTP

## 9) Checklist Email Go-Live
- [ ] SMTP host benar
- [ ] Username/password benar
- [ ] IS_DEVELOPMENT = false
- [ ] Recipient email resmi instansi
- [ ] Test permohonan & keberatan berhasil
