# Lafagen — Desain Sistem Aplikasi Laporan Proker Komunitas FAD & GENRE

Tanggal: 2026-09-10
Status: Disetujui user (diskusi brainstorming 3 putaran)
Lokasi proyek: `D:/laragon/www/laravel/lafagen`

## 1. Ringkasan

Lafagen adalah web application untuk pencatatan dan pelaporan program kerja (proker)
dua komunitas di Kabupaten Tanah Laut: **FAD (Forum Anak Daerah)** dan **GENRE**.
Satu codebase, dua "wajah": landing page memilih komunitas, halaman login bertema
berbeda per komunitas, lalu area aplikasi (dashboard + laporan) dengan warna/branding
komunitas masing-masing. Data kedua komunitas terisolasi penuh satu sama lain.

## 2. Keputusan Arsitektural (kontrak)

| Keputusan | Pilihan | Alasan |
|---|---|---|
| Backend | **Laravel 11, PHP 8.3** | User menyetujui upgrade PHP lokal (Laragon) dari 8.1; Laravel 11 butuh ≥8.2 |
| Frontend | **Inertia.js v1 + Vue 3 (`<script setup>`) + Vite**, rendering client-side only (tanpa SSR) | Satu repo satu server; auth/session/routing murni Laravel; halaman = komponen Vue |
| UI kit | **Tailwind CSS + shadcn-vue**, tema via CSS variables | Permintaan user (shadcn vue) |
| DB | **MySQL 8** (`lafagen`), lokal Laragon | Sudah tersedia |
| Auth | Laravel session-based, **email + password**; TIDAK ada register publik | Akun dibuat Admin |
| Grafik | `recharts` via chart wrapper shadcn-vue (`shadcn-vue` charts) | Kompatibel tema Tailwind |
| Export | `maatwebsite/excel` | Sudah diputuskan |
| Roles | `admin`, `anggota` (per komunitas). Tanpa superadmin lintas komunitas, tanpa alur verifikasi/approval laporan | Keputusan user |

## 3. Alur Pengguna

1. `/` — landing: dua kartu besar FAD | GENRE (branding + logo).
2. `/fad/login` (tema teal) atau `/genre/login` (tema biru) — form email+password.
3. Login sukses → `/dashboard` dalam prefix komunitasnya.
4. Menu sidebar dalam aplikasi: **Dashboard**, **Laporan**, **Kategori** (admin),
   **Pengguna** (admin). Tombol logout + badge nama komunitas.
5. Cross-community ditolak: akun FAD yang membuka `/genre/...` di-redirect paksa ke
   area `/fad/...` miliknya sendiri (bukan 403 mentah).

## 4. Isolasi Komunitas & Tema: Path Prefix

- Route group berprefiks `/fad` dan `/genre`, parameter `{community}` dengan
  `where('community', 'fad|genre')`; middleware `EnsureCommunity` memvalidasi
  kesesuaian `auth()->user()->community` terhadap prefix.
- Konfigurasi komunitas (label, warna, logo) di satu config `communities.php`:
  `fad` → hijau/teal (`oklch/hsl` primary), `genre` → biru/cyan; logo placeholder di
  `public/images/{fad,genre}.png` (user mengganti file = branding berubah, tanpa kode).
- Tema diterapkan lewat atribut `data-community` pada `<html>` + blok CSS variables
  per komunitas; komponen shadcn-vue membaca variables ini sehingga otomatis berubah.
- Gunakan Skill UI UX Pro Max, agar tidak AI Slop / AI Generic

## 5. Skema Data

Enum komunitas: `fad`, `genre` (kolom `community` di `users`, `reports`, `categories`).

### users
- `id`, `name`, `email` (unique global), `password`, `remember_token`
- `community` enum(`fad`,`genre`)
- `role` enum(`admin`,`anggota`), default `anggota`
- timestamps

### categories
- `id`, `community` enum, `name` string
- unique(`community`,`name`)
- Seed default per komunitas: Pendidikan, Sosial & Kemasyarakatan, Lingkungan Hidup,
  Keagamaan, Keterampilan, Lainnya

### reports
- `id`, `community` enum (denormalized untuk index & keamanan scope)
- `user_id` FK → users (pembuat / pelapor)
- `category_id` FK → categories
- `title` string(150)
- `description` text
- `start_date` date
- `end_date` date nullable (proker multi-hari; null = satu hari)
- `location` string(150) nullable
- timestamps
- Index: (`community`,`start_date`), (`community`,`category_id`)

### report_photos
- `id`, `report_id` FK cascade
- `path` string, `original_name` string
- timestamps

Tanpa kolom `status` (keputusan: tanpa verifikasi — laporan langsung tercatat).
Satu laporan = satu pelaksanaan (tanpa entitas induk Proker; dapat ditambahkan nanti
tanpa mengubah struktur yang ada — catatan evolusi, bukan scope).

## 6. Otorisasi (Policy)

Aksi laporan (semua terscope komunitas via `where('community', ...)` di query +
Gate di Policy):

| Aksi | anggota | admin |
|---|---|---|
| Lihat daftar/detail laporan komunitasnya | ✅ | ✅ |
| Buat laporan | ✅ | ✅ |
| Edit / hapus laporan **miliknya** | ✅ | ✅ |
| Edit / hapus laporan **milik orang lain** | ❌ | ✅ |
| CRUD kategori | ❌ | ✅ |
| CRUD pengguna (buat/edit/hapus, reset password) | ❌ | ✅ |
| Export Excel | ✅ | ✅ |

Membuat user: admin memilih komunitas (terkunci ke komunitasnya sendiri) + role.
Admin tidak bisa menurunkan/menghapus dirinya sendiri bila menjadi admin terakhir
di komunitasnya (guard `last-admin`).

## 7. Fitur Rinci

### 7.1 CRUD Laporan
- Form (dialog atau halaman): judul*, kategori (select), tanggal mulai*, tanggal
  selesai (opsional, validasi ≥ mulai), lokasi, deskripsi*, foto berganda
  (jpg/png/webp, maks 5 MB per file, maks 10 file; drag-drop preview; file lama bisa
  dihapus dari form edit).
- Index: tabel (judul, kategori, tanggal, lokasi, pelapor, jumlah foto), filter
  bulan+tahun + kategori + pencarian kata kunci, paginasi 15/halaman, sortable
  tanggal.
- Detail: seluruh field + galeri foto + tombol edit/hapus sesuai hak.

### 7.2 Dashboard
- Kartu: total laporan, jumlah anggota, laporan bulan berjalan, laporan tahun berjalan.
- Bar chart laporan per bulan (tahun berjalan, 12 bulan, Nol-kan yang kosong).
- Donut chart per kategori (tahun berjalan).
- Daftar 5 laporan terbaru.
- Semuanya terscope komunitas user login; ada pemilih tahun.

### 7.3 Upload Foto
- Disimpan di `storage/app/public/reports/{community}/{report_id}/{uuid}.ext`.
- `php artisan storage:link`; URL via `asset('storage/...')`.
- Validasi ekstensi+MIME server-side; rename uuid (jangan percaya nama asli untuk path).

### 7.4 Export Excel
- Tombol "Export Excel" di index laporan **menghormati filter aktif**
  (query yang sama, tanpa paginasi).
- Kolom: No, Judul, Kategori, Tanggal (mulai–selesai), Lokasi, Pelapor,
  Deskripsi (potong ±200 char), Tanggal dibuat.
- Header sheet berisi nama komunitas + rentang filter.

## 8. Struktur Direktori Kunci

```
app/
  Http/Controllers/ (Auth, Dashboard, Report, Category, User, Export)
  Http/Middleware/EnsureCommunity.php, EnsureAdmin.php
  Http/Requests/ (StoreReportRequest, UpdateReportRequest, StoreUserRequest, ...)
  Models/ (User, Category, Report, ReportPhoto)
  Policies/ (ReportPolicy, CategoryPolicy, UserPolicy)
  Enums/ (Community, UserRole)
  Exports/ReportExport.php
config/communities.php
resources/
  views/app.blade.php (inject data-community + props)
  js/
    Pages/{Landing,Auth,Dashboard,Reports,Categories,Users}/
    Layouts/CommunityLayout.vue (sidebar+topbar bertema, dari AppLayout Inertia)
    Components/ (shadcn-vue ui + aplikasi)
    lib/theme.ts, Composables/useCommunity.js
database/seeders/ (AdminUsers, DefaultCategories, DemoReports)
```

## 9. Lingkungan & Dev Workflow

1. User upgrade PHP Laragon ke 8.3 (atau 8.2) — satu-satunya aksi manual user.
2. `composer create-project laravel/laravel . "11.*"` lalu feature-by-feature.
3. `.env`: DB `lafagen`, `APP_URL=http://lafagen.test` (vhost Laragon) atau via port.
4. `php artisan migrate --seed`, `npm run dev` / `npm run build`.
5. kredensial seed demo didokumentasikan di README (email admin kedua komunitas).

Deploy target: belum ditentukan; desain tidak mengasumsikan hosting tertentu
(persyaratan: PHP 8.2+, MySQL, Composer, Node utk build, writeable storage,
`storage:link`).

## 10. Di Luar Scope v1

- Register mandiri / pemulihan password via email (reset password = admin reset manual).
- Alur verifikasi/approval laporan.
- Entitas induk Proker multi-pelaksanaan.
- Superadmin lintas komunitas.
- Notifikasi, komentar, lampiran non-gambar (PDF dll).

## 11. Risks

- **PHP upgrade** adalah prasyarat eksternal (user); kalau gagal, fallback Laravel 10
  tanpa perubahan desain (Inertia/Vue sama).
- Isolasi lintas komunitas bergantung pada dua lapis: middleware prefix + scope
  `community` di query; semua test menjangkau kasus akun FAD memanggil endpoint GENRE.
- Upload foto lokal: backup filesystem bukan tanggung jawab aplikasi; dokumentasikan.
