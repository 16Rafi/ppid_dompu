# PPID Kabupaten Dompu - Documentation

Dokumentasi ini dibuat untuk serah terima sistem PPID kepada instansi pemerintah yang akan melakukan konfigurasi dan deployment sendiri.

**Isi Dokumentasi**
1. Ringkasan Sistem
**Teknologi/Framework**\n- Backend: PHP (tanpa framework)\n- Database: MySQL/MariaDB\n- Frontend: HTML, CSS, JavaScript (tanpa framework)\n\n
2. Fitur Utama
3. Struktur Proyek
4. Persyaratan Server
5. Instalasi Lokal
6. Konfigurasi Penting
7. Database dan Skema
8. Modul Admin
9. Halaman Publik
10. Upload dan File
11. Keamanan
12. Deployment Produksi
13. Troubleshooting

**Ringkasan Sistem**
Website PPID (Pejabat Pengelola Informasi dan Dokumentasi) berbasis PHP, MySQL, HTML/CSS/JS. Sistem mencakup publikasi berita, halaman dinamis, permohonan informasi, pengajuan keberatan, serta daftar informasi publik (DIP). Admin panel digunakan untuk mengelola semua konten.

**Fitur Utama**
- Berita (CRUD) dengan sanitasi HTML.
- Halaman dinamis berbasis block di `pages/template.php?slug=...`.
- Permohonan informasi publik dan pengajuan keberatan.
- DIP (Daftar Informasi Publik) dengan statistik dan pagination.
- Menu navigasi yang bisa diatur dari admin.
- Website eksternal (perangkat daerah, portal, dll).
- Export laporan (permohonan dan keberatan) ke CSV.
- Keamanan: CSRF, audit log, hardening upload, security headers.

**Struktur Proyek**
```
ppid_dompu/
  admin/
    index.php
    dashboard.php
    laporan.php
    export_laporan.php
    export_laporan_keberatan.php
    permohonan/
    keberatan/
    dip/
    menus/
    pages/
  css/style.css
  js/script.js
  includes/
    config.php
    security_headers.php
    permohonan_service.php
    keberatan_service.php
    file_upload.php
    email_service.php
  pages/
    template.php
    dip.php
    permohonan-informasi.php
    pengajuan-keberatan.php
    daftar-permohonan-publik.php
    daftar-keberatan-publik.php
  uploads/
  database/database.sql
  index.php
```

**Persyaratan Server**
- PHP 8.1+ (disarankan 8.2)
- MySQL/MariaDB
- Apache/Nginx
- Ekstensi PHP: `mysqli`, `mbstring`, `fileinfo`, `openssl`
- Composer (untuk dependency HTMLPurifier dan PHPMailer)

**Instalasi Lokal (XAMPP contoh)**
1. Ekstrak proyek ke `C:\xampp\htdocs\ppid_dompu`.
2. Jalankan `composer install` di root proyek.
3. Buat database `db_ppid_dompu`.
4. Import `database/database.sql`.
5. Buka `http://localhost/ppid_dompu`.

**Konfigurasi Penting**
File: `includes/config.php`
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
- `BASE_URL` wajib disesuaikan dengan domain produksi.

Contoh:
```
// http://domain-anda/ppid
define('BASE_URL', 'https://ppid.domain.go.id/');
```

Email:
- Lihat `EMAIL_SETUP.md`
- Konfigurasi di `includes/email_config.php`

**Database dan Skema (Ringkasan)**
Tabel utama:
- `users` admin login.
- `news` berita.
- `pages` halaman dinamis (judul, slug, content legacy).
- `page_blocks` + subtable: `page_text_blocks`, `page_table_blocks`, `page_table_columns`, `page_table_rows`, `page_table_cells`, `page_files`, `page_links`, `page_image_blocks`.
- `menus` navigasi.
- `settings` setting situs.
- `permohonan_informasi` permohonan publik.
- `log_status_permohonan` riwayat status permohonan.
- `keberatan` pengajuan keberatan.
- `log_status_keberatan` riwayat status keberatan.
- `daftar_informasi_publik` (DIP).
- `files` metadata lampiran.
- `external_websites` website eksternal.

Catatan migration:
- Jika DB lama masih memakai struktur keberatan lama (`permohonan_id`, `alasan_keberatan`), lakukan rename dan buat tabel baru seperti di dokumentasi migrasi.

**Modul Admin**
URL admin: `BASE_URL/admin/index.php`

Modul:
- Dashboard: `admin/dashboard.php`
- Permohonan: `admin/permohonan/index.php`
- Keberatan: `admin/keberatan/index.php`
- DIP: `admin/dip/index.php`
- Pages: `admin/pages/index.php`
- Menus: `admin/menus/index.php`
- Laporan: `admin/laporan.php`
- Export CSV permohonan: `admin/export_laporan.php`
- Export CSV keberatan: `admin/export_laporan_keberatan.php`

**Halaman Publik**
- Beranda: `index.php`
- Berita: `pages/berita.php`
- Detail berita: `pages/berita-detail.php?slug=...`
- Template halaman dinamis: `pages/template.php?slug=...`
- Permohonan informasi: `pages/permohonan-informasi.php`
- Pengajuan keberatan: `pages/pengajuan-keberatan.php`
- DIP: `pages/dip.php`
- Daftar permohonan publik: `pages/daftar-permohonan-publik.php`
- Daftar keberatan publik: `pages/daftar-keberatan-publik.php`

**DIP**
- Admin input DIP di `admin/dip/index.php`.
- DIP publik di `pages/dip.php`.
- Home menampilkan DIP terbaru maksimum 5 baris.

**Upload dan File**
- Folder upload: `uploads/`
- Upload divalidasi dengan MIME dan ekstensi.
- File metadata tersimpan di tabel `files`.

**Keamanan**
- CSRF token di admin.
- Rate limit login.
- Audit log admin.
- Security headers (CSP, XFO, HSTS, dll).
- Sanitasi HTML untuk konten berita.

**Deployment Produksi**
1. Set `BASE_URL` ke domain publik.
2. Set konfigurasi email SMTP.
3. Pastikan permission `uploads/` bisa ditulis oleh web server.
4. Import database ke server.
5. Aktifkan HTTPS dan validasi CSP jika ada perubahan domain.
6. Verifikasi semua URL menu.

**Troubleshooting**
- Halaman kosong: cek error PHP di server.
- Upload gagal: cek permission folder `uploads/`.
- Menu error: cek validasi di admin menu dan URL.
- Export CSV rusak: pastikan tidak ada warning PHP (disable display_errors di produksi).

**Catatan Serah Terima**
- Semua CSS/JS sudah digabung di `css/style.css` dan `js/script.js`.
- Halaman dinamis menggunakan `pages/template.php?slug=...`.
- DIP dan Keberatan sudah memakai struktur baru.

