# PPID Kabupaten Dompu

Website PPID (Pejabat Pengelola Informasi dan Dokumentasi) untuk Kabupaten Dompu yang dibangun dengan HTML, CSS, PHP, dan JavaScript dengan database MySQL.

## Fitur

- **Header Sticky**: Header yang mengikuti scroll dengan menu navigasi lengkap
- **Hero Section**: Tampilan hero dengan gambar Gedung Kominfo Kabupaten Dompu
- **Website Pemerintah**: Menampilkan website-website pemerintah Kabupaten Dompu lainnya
- **Berita Terkini**: Menampilkan berita-berita terbaru dengan format yang menarik
- **Admin Panel**: Sistem login admin untuk mengelola konten website
- **Responsive Design**: Tampilan yang optimal di berbagai perangkat
- **Color Palette**: Menggunakan palet warna yang telah ditentukan
- **Keamanan Lengkap**: CSRF protection, rate limiting, audit log, HTML sanitasi, upload hardening

## Instalasi

### 1. Persyaratan

- XAMPP (Apache, MySQL, PHP)
- Composer (untuk dependency management)
- Web browser modern

### 2. Langkah Instalasi

1. **Ekstrak folder project** ke dalam `htdocs` XAMPP Anda:
   ```
   C:\xampp\htdocs\ppid_dompu\
   ```

2. **Install Composer Dependencies**:
   - Buka terminal/command prompt di folder project
   - Jalankan perintah:
     ```bash
     composer install
     ```
   - Ini akan menginstall HTMLPurifier untuk sanitasi konten berita

3. **Import Database**:
   - Buka phpMyAdmin (http://localhost/phpmyadmin)
   - Buat database baru dengan nama `db_ppid_dompu`
   - Import file `database.sql` yang ada di folder project

4. **Konfigurasi**:
   - Pastikan konfigurasi database di `includes/config.php` sudah benar
   - Default database settings:
     - Host: localhost
     - Username: root
     - Password: (kosong)
     - Database: db_ppid_dompu

5. **Akses Website**:
   - Start Apache dan MySQL di XAMPP
   - Buka browser dan akses: `http://localhost/ppid_dompu`

### 3. Login Admin

- URL: `http://localhost/ppid_dompu/admin/index.php`
- Username: `admin`
- Password: `admin123`

## Struktur Folder

```
ppid_dompu/
├── admin/                  # Halaman admin
│   ├── index.php           # Login admin
│   ├── dashboard.php       # Dashboard admin
│   └── logout.php          # Logout
├── css/
│   └── style.css          # Stylesheet utama
├── js/
│   └── script.js          # JavaScript utama
├── pages/                  # Halaman statis
│   ├── template.php       # Template halaman
│   └── berita-detail.php  # Detail berita
├── includes/
│   └── config.php         # Konfigurasi database
├── img/                   # Folder gambar
├── uploads/               # Folder upload gambar
├── index.php              # Halaman utama
└── database.sql           # File database SQL
```

## Color Palette

Website menggunakan palet warna berikut:

- **#093A5A**: Dark Blue (header, background utama)
- **#A0861D**: Gold/Brown (aksen, hover)
- **#F4B800**: Bright Yellow/Orange (highlight, buttons)
- **#7392A8**: Light Blue/Grey (text sekunder)
- **#FCFDFD**: Off-white (background konten)

## Fitur Menu

### Menu Utama
- **BERANDA**: Halaman utama
- **DIP**: Daftar Informasi Publik
- **PROFIL PPID**: Profil PPID (dengan dropdown)
  - Visi Misi
  - Struktur Organisasi
  - Tugas dan Fungsi
- **PROSEDUR**: Prosedur layanan (dengan dropdown)
  - Permohonan Informasi
  - Keberatan Informasi
- **KEUANGAN**: Informasi keuangan
- **LHKPN & LHKASN**: Laporan harta kekayaan
- **UNDUH**: Halaman download
- **SKM**: Survei Kepuasan Masyarakat
- **FKP**: Forum Konsultasi Publik
- **SP**: Standar Pelayanan
- **KONTAK**: Halaman kontak
- **LOGIN**: Login admin

## Database

Database terdiri dari tabel-tabel berikut:

- `users`: Data pengguna admin
- `news`: Data berita/artikel
- `pages`: Data halaman statis
- `menus`: Data menu navigasi
- `settings`: Pengaturan website
- `external_links`: Link website eksternal
- `documents`: Data dokumen/download

## Customization

### Mengubah Hero Image
Ganti URL gambar di `index.php` pada bagian hero section:
```html
<img src="https://via.placeholder.com/1920x600/093A5A/FFFFFF?text=Gedung+Kominfo+Kabupaten+Dompu" alt="Gedung Kominfo Kabupaten Dompu">
```

### Mengubah Informasi Website
Edit data di tabel `settings` melalui phpMyAdmin atau admin panel.

### Menambah/Mengedit Menu
Kelola menu melalui tabel `menus` atau melalui admin panel.

## Support

Jika mengalami masalah:

1. Pastikan XAMPP berjalan dengan baik
2. Periksa konfigurasi database di `includes/config.php`
3. Pastikan database sudah diimport dengan benar
4. Clear cache browser jika tampilan tidak update

## Keamanan

Website ini dilengkapi dengan fitur keamanan lengkap:
- ✅ **CSRF Protection** untuk semua form admin
- ✅ **Rate Limiting Login** (5x attempt → 15m lockout)
- ✅ **Audit Log** untuk semua aktivitas admin
- ✅ **HTML Sanitization** dengan HTMLPurifier
- ✅ **Upload Hardening** (image/video only, MIME validation)
- ✅ **Session Hardening** (HttpOnly, SameSite, Secure)
- ✅ **Security Headers** (CSP, X-Frame-Options, dll)

## Deployment

Untuk instruksi deployment ke production server, lihat:
📖 **[Deployment Guide](docs/DEPLOYMENT.md)**

### Quick Deploy Checklist:
- [ ] Install SSL certificate (HTTPS wajib)
- [ ] Update BASE_URL ke HTTPS
- [ ] Setup backup otomatis
- [ ] Configure monitoring
- [ ] Test semua fitur keamanan

## License

Project ini dibuat untuk PPID Kabupaten Dompu.
