# Lafagen

Sistem pelaporan program kerja (proker) dua komunitas di Kabupaten Tanah Laut:
**FAD (Forum Anak Daerah)** dan **GENRE (Generasi Berencana)** — satu aplikasi,
dua tema/area terpisah, data antar komunitas terisolasi.

## Fitur

- Landing pemilihan komunitas, halaman login bertema (FAD hijau-teal, GENRE biru).
- CRUD laporan proker: judul, kategori, tanggal mulai/selesai, lokasi, deskripsi,
  unggah banyak foto dokumentasi (JPG/PNG/WEBP, maks 5 MB/foto, maks 10 foto).
- Filter laporan per bulan/tahun/kategori + pencarian, paginasi 15/halaman.
- Dashboard: total laporan, jumlah anggota, laporan bulan/tahun berjalan,
  grafik batang per bulan, donut per kategori, 5 laporan terbaru, pemilih tahun.
- Manajemen kategori dan pengguna (khusus admin komunitas).
- Export Excel mengikuti filter yang aktif di halaman laporan.
- Hak akses: `anggota` mengelola laporannya sendiri; `admin` mengelola seluruh
  laporan, kategori, dan pengguna dalam komunitasnya.

## Stack

Laravel 11 · PHP ≥ 8.2 (dikembangkan di 8.3) · Inertia.js v2 · Vue 3 · Vite ·
Tailwind CSS 3.4 + shadcn-vue · MySQL 8 (dev) / SQLite in-memory (test) ·
maatwebsite/excel 3.1 · PHPUnit.

## Setup

```bash
composer install
copy .env.example .env      # Windows
php artisan key:generate

mysql -u root -e "CREATE DATABASE IF NOT EXISTS lafagen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
# sesuaikan DB_DATABASE/DB_USERNAME/DB_PASSWORD di .env

php artisan migrate --seed
php artisan storage:link     # agar foto di storage/app/public bisa diakses
npm install
npm run dev                  # atau: npm run build
php artisan serve
```

### Kredensial seed

| Komunitas | Email | Password |
|---|---|---|
| FAD | `admin@lafagen.test` | `password` (atau `SEED_ADMIN_PASSWORD` di `.env`) |
| GENRE | `admin-genre@lafagen.test` | idem |

Seeder juga membuat 6 kategori default per komunitas dan 20 laporan demo
(12 FAD + 8 GENRE) agar dashboard langsung berisi grafik.

### Aset brand

Logo komunitas memakai `public/images/fad.png` dan `public/images/genre.png`
(saat ini placeholder 1×1). Ganti dua file itu dengan logo asli — tidak ada
perubahan kode yang diperlukan. Nama/warna komunitas diatur di `config/communities.php`.

## Test

```bash
php artisan test          # 45 test: auth, isolasi komunitas, laporan, dashboard, kategori, pengguna, export
npm run build             # verifikasi build frontend
```

## Catatan operasional

- Foto laporan tersimpan di `storage/app/public/reports/{komunitas}/{id}/`.
  **Backup folder `storage/app/`** bersama database; `public/storage` hanya symlink.
- Password user hanya bisa dibuat/di-reset oleh admin komunitas (tidak ada
  registrasi publik maupun reset via email).
- Isolasi komunitas dijaga dua lapis: prefix URL `/fad` & `/genre` (middleware
  `EnsureCommunity`) dan kolom `community` pada setiap query data bisnis.
