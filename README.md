# Lafagen

Sistem pelaporan program kerja (proker) dua komunitas di Kabupaten Tanah Laut:
**FAD (Forum Anak Daerah)** dan **GENRE (Generasi Berencana)** — satu aplikasi,
dua tema/area terpisah, data antar komunitas terisolasi.

## Fitur

- Landing pemilihan komunitas dengan statistik tiap komunitas; halaman login dua
  panel bertema (FAD hijau-teal, GENRE biru).
- Navigasi responsif: sidebar ber-ikon di desktop, bottom bar dengan FAB tengah di
  ponsel (termasuk Kategori & Pengguna untuk admin).
- CRUD laporan proker: judul, kategori, tanggal mulai/selesai, lokasi, deskripsi,
  unggah banyak foto dokumentasi lewat dropzone seret-lepas (JPG/PNG/WEBP,
  maks 5 MB/foto, maks 10 foto).
- Filter laporan per bulan/tahun/kategori + pencarian dengan chip filter aktif,
  paginasi 15/halaman berupa tautan `<a>` sungguhan (bisa klik-tengah/Ctrl+klik).
- Dashboard: sapaan, 4 kartu statistik ber-ikon + tren vs bulan lalu, grafik batang
  per bulan, donut per kategori, panel aktivitas + streak, dan 5 laporan terbaru.
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
php artisan test          # 53 test: auth, isolasi komunitas, laporan, dashboard, streak, kategori, pengguna, export
npm run build             # verifikasi build frontend
```

`phpunit.xml` memaksa `APP_ENV=testing` + SQLite in-memory lewat `force="true"`
**dan** blok `<server>`. Keduanya wajib: PHPUnit hanya menulis `<env>` ke
`putenv()`/`$_ENV`, sedangkan Laravel membaca `$_SERVER` lebih dulu, sehingga
tanpa mirror itu suite bisa ikut memakai MySQL pengembangan dan `RefreshDatabase`
akan menghapus isinya.

## Desain

Palet, tipografi, dan komponen mengikuti spec
`docs/superpowers/specs/2026-09-13-lafagen-ui-ux-design.md` (gaya "Energik
Sporty" untuk audiens anak & remaja). Warna tidak di-hardcode di komponen:
semua dibaca dari CSS variables (`--primary`, `--chart-1..5`, `--success`,
dst.) yang di-override per komunitas lewat atribut `data-community` pada
`<html>`. Ganti tema cukup di `resources/css/app.css`.

## Catatan operasional

- Foto laporan tersimpan di `storage/app/public/reports/{komunitas}/{id}/`.
  **Backup folder `storage/app/`** bersama database; `public/storage` hanya symlink.
- Password user hanya bisa dibuat/di-reset oleh admin komunitas (tidak ada
  registrasi publik maupun reset via email).
- Isolasi komunitas dijaga dua lapis: prefix URL `/fad` & `/genre` (middleware
  `EnsureCommunity`) dan kolom `community` pada setiap query data bisnis.
