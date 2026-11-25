# News Hotel

Situs promosi dan blog untuk tiga hotel: **News Hotel Surabaya**, **News Hotel Semarang**, dan **Darmo Inn by News Hotel**.

- Tanpa pemesanan online dan tanpa akun pengunjung. Setiap ajakan reservasi berupa tautan WhatsApp (atau telepon dan petunjuk arah bila nomor belum ada).
- PHP native tanpa framework: satu URL berarti satu file `.php`, tanpa router, class, atau Composer.
- Konten disimpan di MariaDB/MySQL dan dikelola satu admin lewat panel admin (atau phpMyAdmin).
- Dua bahasa: Indonesia dan English (`/en/...`).

## Struktur

```
frontend/    Situs publik (web root domain utama)
backend/     Panel admin (web root subdomain admin)
shared/      Kode bersama: config, db, fungsi bantu, bahasa, Parsedown
database/    newshotel.sql (struktur + isi)
scripts/     Alat terminal: optimasi gambar, build aset, buat akun admin
docs/        Foto dan logo sumber (tidak disajikan ke publik)
```

`frontend/` dan `backend/` masing-masing berdiri sendiri dengan `.htaccess` sendiri. `shared/`, `database/`, dan `scripts/` berada di luar kedua web root dan diblokir dari web.

Pola setiap halaman publik:

```php
<?php
require_once __DIR__ . '/../includes/init.php';
$hotel = hotel('surabaya');
$page  = ['title' => $hotel['name'], 'hotel' => $hotel];
require FRONT . '/includes/header.php';
?>
... HTML halaman ...
<?php require FRONT . '/includes/footer.php'; ?>
```

`.htaccess` membuat URL bersih: `/surabaya/kamar` → `surabaya/kamar.php`, `/en/...` → `?lang=en`, `/blog/judul` → `blog/artikel.php?slug=judul`.

## Menjalankan di XAMPP

1. Jalankan Apache dan MySQL.
2. Impor database (membuat database `newshotel`):
   ```
   C:\xampp\mysql\bin\mysql.exe -u root < database\newshotel.sql
   ```
3. Buat akun admin (dump tidak menyertakan akun):
   ```
   C:\xampp\php\php.exe scripts\create-admin.php --password "kata-sandi-baru"
   ```
4. Buka situs di `http://localhost/newshotel-v01/frontend/` dan panel admin di `http://localhost/newshotel-v01/backend/`.

## Hosting

Arahkan domain utama ke `frontend/` dan subdomain admin ke `backend/`. Buat `shared/config.local.php` (tidak ikut git) untuk menimpa `shared/config.php`:

```php
<?php
return [
    'debug'    => false,
    'db'       => ['name' => 'nama_db', 'user' => 'user_db', 'pass' => 'rahasia'],
    'site_url' => 'https://domain-anda.com',
];
```

- **`debug`**: bawaannya `null` (detail error hanya tampil untuk permintaan dari komputer sendiri). Di hosting set `false`.
- **`site_url`**: alamat situs publik. Wajib bila admin ada di domain lain, agar gambar dan tautan "Lihat situs" di admin benar.
- **`serve_gz`**: file `.gz` dari `scripts/build-assets.php` hanya disajikan di Apache. Di nginx/LiteSpeed set `false`.
- Jalankan `php scripts/build-assets.php` setelah clone atau deploy, dan setiap kali CSS/JS diubah. File `.gz` hasilnya tidak ikut git; tanpa itu situs tetap jalan, hanya tidak terkompresi.
- Disarankan: HTTPS, HSTS, dan CSP (perlu `frame-src https://www.google.com`).

## Panel admin

Satu akun admin mengelola ketiga hotel: profil dan kontak hotel, kamar dan foto, galeri, blog, fasilitas, tempat sekitar, dining, ruang meeting, dan pengaturan situs.

- `scripts/create-admin.php` membuat atau mengubah akun itu (`--username`, `--email`, `--name`, `--password`).
- Kata sandi kurang dari 10 karakter hanya diterima bila panel dibuka dari komputer server sendiri. **Ganti sebelum situs online.**
- Mengimpor ulang `database/newshotel.sql` menghapus akun admin; buat lagi dengan skrip di atas.
- Foto JPG/PNG/WebP (maks. 5 MB) yang diunggah otomatis dibuat menjadi WebP berbagai ukuran.
- Nomor WhatsApp, telepon, dan email diisi di **Profil hotel**. Setelah terisi, tombol di situs otomatis memakainya.

## Gambar

Kolom gambar menerima kunci gambar teroptimasi (mis. `surabaya/lobby` → `frontend/assets/img/surabaya/lobby-480.webp`, dst.) atau path file biasa di dalam `frontend/assets/`.

Membuat varian WebP dari foto sumber:

1. Daftarkan foto di `scripts/image-map.php`.
2. Jalankan `php scripts/optimize-images.php` (`--force` untuk membangun ulang semuanya).
3. Setelah mengubah CSS/JS, jalankan `php scripts/build-assets.php`.

## Konten lewat phpMyAdmin

Kolom tanpa akhiran berisi teks Indonesia; kolom `*_en` berisi English dan, bila kosong, halaman English memakai teks Indonesia.

| Tabel | Isi |
|---|---|
| `hotels` | Profil hotel: alamat, WhatsApp, telepon, email, Instagram, deskripsi, kebijakan (Markdown), foto |
| `room_types`, `room_images` | Tipe kamar, tarif mulai (`base_price`), fasilitas (JSON), foto |
| `facilities`, `attractions` | Fasilitas hotel dan tempat sekitar |
| `dining_outlets`, `meeting_venues` | Restoran/kafe/bar dan ruang meeting |
| `gallery_images` | Foto galeri per hotel |
| `posts` | Artikel blog: `category` (`news`, `event`, `promo`), `hotel_id` (kosong = artikel grup), `body` dalam Markdown |
| `settings` | Pengaturan situs dari panel admin |
