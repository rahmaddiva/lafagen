# Lafagen — Desain Ulang UI/UX (Gaya "Energik Sporty")

Tanggal: 2026-09-13
Status: Disetujui user (brainstorming 7 putaran)
Prasyarat: `docs/superpowers/specs/2026-09-10-lafagen-design.md` (desain sistem fungsional)
Lokasi proyek: `D:/laragon/www/laravel/lafagen`

## 1. Ringkasan & Sasaran

Aplikasi Lafagen sudah berfungsi penuh (45 test hijau) tetapi tampilannya masih
default shadcn-vue: radius `0.5rem`, abu-abu netral, satu font, sidebar hanya untuk
desktop, dan tabel lebar di semua ukuran layar. Sasaran pengguna adalah **anak
komunitas dan remaja** (FAD & GENRE Kab. Tanah Laut), yang hampir seluruhnya membuka
aplikasi dari ponsel.

Desain ulang ini menetapkan satu arah visual — **Energik Sporty**: kontras tinggi,
tipografi besar dan tebal, aksen pekat, sudut sedang, shadow tegas, transisi cepat —
lalu menerapkannya ke seluruh halaman. Identitas per komunitas tetap **hanya** warna
token + logo (struktur, radius, shadow, dan tipografi identik untuk FAD dan GENRE).

Bukan sasaran: fitur baru, perubahan alur bisnis, perubahan otorisasi, atau
penambahan pustaka grafik.

## 2. Keputusan yang Disetujui User

| # | Pertanyaan | Pilihan user |
|---|---|---|
| 1 | Arah visual | **Energik Sporty** |
| 2 | Identitas FAD vs GENRE | **Token warna + logo saja** |
| 3 | Navigasi mobile | **Bottom tab bar + sidebar desktop** |
| 4 | Komponen dashboard | **Chart diperbaiki, stat card ber-ikon + tren, panel aktivitas/streak, greeting hero** — 4 dari 7 opsi |
| 5 | Pola daftar | **Tabel di desktop, kartu di mobile** |
| 6 | Cakupan | **Semua halaman, satu putaran** |
| 7 | Sumber data dashboard | **Hitung dari data yang sudah ada** (tanpa migrasi) |
| 8 | Animasi | **Transisi halus + animasi masuk** (CSS saja) |
| 9 | Tipografi | **Plus Jakarta Sans** |
| 10 | Cara memuat font | **Google Fonts `<link>`** (+ preconnect, `display=swap`) |
| 11 | Blok `.dark` (kode mati) | **Sinkronkan token `.dark`, tanpa verifikasi kontras** |

### Secara eksplisit TIDAK termasuk

Tiga opsi dashboard yang **tidak** dipilih user, dan satu opsi yang tidak dipilih
tetapi sempat masuk draf desain ini (dicatat agar tidak diam-diam kembali):

- ❌ **Goal / progress bulan ini** — tidak dipilih. Tidak ada konsep target/goal,
  tidak ada komponen progress bar, tidak ada penambahan config target.
- ❌ **Laporan terbaru jadi kartu + thumbnail** — tidak dipilih. Daftar "5 laporan
  terbaru" tetap baris teks sederhana; tidak ada `photo_url` di props `recent`.
- ❌ **Dark mode lolos kontras** — tidak dipilih. `.dark` tidak dapat diaktifkan
  (lihat §3.5), jadi tidak ada anggaran verifikasi kontras untuknya.
- ❌ **Pustaka grafik baru** — `package.json` tidak punya pustaka chart. Chart tetap
  SVG tulis-tangan; perbaikan bersifat aditif.

## 3. Temuan Audit (kondisi awal, terverifikasi)

Semua temuan di bawah diverifikasi langsung dari repo/mesin ini, bukan dugaan.

### 3.1 Font tidak pernah dimuat — bug nyata

`tailwind.config.js:17` mendeklarasikan `Figtree` di `fontFamily.sans`, tetapi:
`grep -rn "Figtree|fonts.googleapis|fonts.bunny"` pada `app.blade.php`, `app.css`,
`app.js`, dan `vite.config.js` → **0 hasil**. Tidak ada `@import`, tidak ada `<link>`,
tidak ada paket `@fontsource`. Probe DOM mengembalikan
`Figtree, ui-sans-serif, system-ui, …` sehingga halaman **benar-benar dirender dengan
font sistem** (Segoe UI di Windows).

Konsekuensi: mengubah `fontFamily.sans` menjadi `'Plus Jakarta Sans'` saja akan
mereproduksi bug yang sama. Perlu jalur pemuatan nyata.

### 3.2 Palet donut memakai token yang tidak di-override

`resources/css/app.css:27-29` mendefinisikan `--chart-3/4/5` hanya pada `:root`
(`197 37% 24%`, `43 74% 66%`, `27 87% 67%`). Blok `[data-community='fad']` dan
`[data-community='genre']` **hanya** meng-override `--chart-1` dan `--chart-2`.
`CategoryDonut.vue` memakai palet 8 warna (`--primary`, `--chart-2` … `--chart-5`,
lalu 3 hex hardcoded `#65a30d`, `#ca8a04`, `#dc2626`). Dengan 6 kategori per
komunitas, irisan ke-3 dan seterusnya tampil dengan warna navy/oranye/kuning bawaan
shadcn — **tidak sesuai tema komunitas mana pun**.

### 3.3 Halaman Landing tidak memakai token sama sekali

`app.blade.php:2` menetapkan `data-community` dari `props.community.key` dengan
fallback `'public'`. `HandleInertiaRequests::share()` mengembalikan `null` untuk
tamu, sehingga Landing dirender dengan `[data-community='public']` — dan **tidak ada
blok CSS untuk `public`**. Namun Landing juga tidak membaca token apa pun: seluruh
warnanya hardcoded (`slate-*`, `teal-*`, `sky-*`). Jadi ini bukan tema rusak,
melainkan **permukaan yang belum bertoken**. Setelah desain ulang (yang memakai
token), celah `public` ini akan mulai terlihat — karena itu blok brand `public`
wajib ditambahkan lebih dulu.

### 3.4 Navigasi mobile tidak lengkap — bug akses

`CommunityLayout.vue` menyembunyikan sidebar pada `<768px` (`hidden … md:flex`).
Penggantinya di header hanya berisi tiga tautan teks: `Dashboard`, `Laporan`, dan
tombol `Keluar`. Akibatnya **admin komunitas tidak dapat membuka halaman Kategori
dan Pengguna dari ponsel** — dua halaman itu hanya ada di sidebar desktop.

### 3.5 Dark mode adalah kode mati

`tailwind.config.js` menyetel `darkMode: ['class']` dan `app.css` punya blok `.dark`,
tetapi `grep` untuk sakelar tema / `prefers-color-scheme` / `class="dark"` di seluruh
`resources/js` dan `resources/views` → **0 hasil**. Tidak ada cara mengaktifkan dark
mode. Kontras dark mode karena itu **tidak dapat diobservasi**, sehingga tidak masuk
anggaran verifikasi (sesuai keputusan #11: token disinkronkan agar tidak rusak).

### 3.6 Inkonsistensi kapitalisasi direktori — gagal build di Linux

Kapitalisasi nyata di disk (diverifikasi dengan `Get-ChildItem -Directory`, karena
filesystem Windows tidak peka huruf besar/kecil sehingga `ls -d` **tidak** dapat
membuktikan apa pun): `components` (huruf kecil), `composables`, `Layouts`, `Pages`.

Jadi masalahnya **hanya pada `@/Components/`** — bukan "semua impor". Impor
`@/Layouts/…` dan `@/Pages/…` sudah benar. Yang harus dinormalisasi ke
`@/components/`:

- `Pages/Dashboard.vue:9-11` → `StatCard`, `charts/MonthlyBar`, `charts/CategoryDonut`
- `Pages/Reports/Index.vue:15` → `EmptyState`
- `Pages/Reports/Form.vue:11` → `PhotoUploader`

Windows tidak peka huruf besar/kecil sehingga lolos; pada server Linux
(case-sensitive) `npm run build` **gagal**. Bukti perbaikan harus berupa pemeriksaan
peka huruf besar/kecil: `grep -rn "@/Components/" resources/js` → **0 hasil**
(`ls -d` tidak bermakna di filesystem yang tidak peka huruf besar/kecil).

Ini **murni penulisan ulang jalur impor** — tidak ada direktori yang di-rename.
`docs/superpowers/plans/2026-09-10-lafagen-implementation.md:1331` juga menyebut
`resources/js/Components/`; itu catatan historis dan **tidak diubah**.

### 3.7 Paginasi bukan tautan

`Reports/Index.vue` merender paginasi sebagai `<Button … @click="router.get(...)"
v-html="link.label">`. Karena bukan `<a>`, tautan tidak bisa dibuka di tab baru,
tidak bisa disalin, dan tidak terindeks. Label juga di-`v-html` mentah.

### 3.8 Dashboard: 12 kueri dalam satu loop

`DashboardController::index()` menjalankan `Report::filtered($community, ['year'=>$year,
'month'=>$m])->count()` untuk `$m = 1..12` — **12 kueri terpisah** hanya untuk data
grafik batang. Data yang sama dapat diambil dengan satu `GROUP BY MONTH(start_date)`.

## 4. Sistem Token & Tipografi

### 4.1 Pemuatan font

`resources/views/app.blade.php` `<head>`, sebelum `@vite`:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
```

`tailwind.config.js`:

```js
fontFamily: {
    sans: ['"Plus Jakarta Sans"', 'Figtree', ...defaultTheme.fontFamily.sans],
    display: ['"Plus Jakarta Sans"', 'Figtree', ...defaultTheme.fontFamily.sans],
},
```

Diukur di mesin ini: `fonts.googleapis.com` merespons `200` dalam **0,48 s**
(dibanding `registry.npmjs.org` **1,30 s** untuk alternatif self-host). Alternatif
`@fontsource-variable/plus-jakarta-sans` ditolak karena menambah dependensi build
~1–2 MB demi aset kosmetik, dan jalur CDN sudah cukup cepat di lingkungan ini.

### 4.2 Radius & shadow

`--radius: 0.5rem` → **`0.75rem`** (sudut sedang; `lg/md/sm` mengikuti otomatis).

`tailwind.config.js` `boxShadow`:
- `card` — `0 1px 2px hsl(var(--foreground) / 0.06), 0 1px 3px hsl(var(--foreground) / 0.10)`
- `lift` — `0 8px 24px -6px hsl(var(--foreground) / 0.18)` (dipakai saat hover)

### 4.3 Token warna — angka kontras terverifikasi

**PENTING — koreksi terhadap draf awal spec ini.** Draf pertama memakai
`FAD 168 84% 30%` dan `GENRE 210 100% 42%`, dan menyimpulkan FAD gagal AA
(4,14:1). Nilai itu **tidak pernah ada di repo** — itu angka yang saya tulis dari
ingatan. Nilai yang benar-benar dipakai `app.css` adalah:

| Token | FAD (sudah ada) | GENRE (sudah ada) | Kontras vs putih |
|---|---|---|---|
| `--primary` | `166 75% 28%` | `205 90% 40%` | **5,07:1** / **4,84:1** — **sudah lolos AA** |
| `--accent-foreground` di atas `--accent` | `166 75% 18%` / `162 73% 92%` | `205 90% 30%` / `204 94% 93%` | **8,44:1** / **6,41:1** |

Karena palet yang ada **sudah lolos AA**, desain ulang ini **mempertahankan nilai
`--primary` dan `--accent` yang sekarang** — tidak ada alasan mengubah warna yang
sudah benar. Perubahan token murni bersifat **aditif**.

Yang ditambahkan (aditif, dengan rasio terhitung):

| Token baru | FAD | GENRE | Catatan |
|---|---|---|---|
| `--primary-soft` | `162 73% 92%` (samakan dgn `--accent` yang ada) | `204 94% 93%` | latar chip/nav aktif |
| `--primary-strong` | `166 75% 18%` (= `--accent-foreground`) | `205 90% 30%` | teks di atas soft |
| `--chart-3` | keluarga teal/emerald | keluarga biru/indigo | **memperbaiki §3.2** |
| `--chart-4` | keluarga teal | keluarga biru | **memperbaiki §3.2** |
| `--chart-5` | keluarga lime | keluarga sky | **memperbaiki §3.2** |
| `--success`, `--warning`, `--info` (+ `-soft`, `-foreground`) | netral, sama utk kedua komunitas | idem | untuk badge/delta |

Blok `public` (baru, untuk Landing) memakai netral Lafagen — dipilih agar tidak
berkompetisi dengan warna komunitas:

```css
[data-community='public'] {
  --primary: 240 5.9% 10%;        /* netral gelap, sama seperti :root */
  --primary-foreground: 0 0% 98%;
  --chart-1..5: <skala netral>;
}
```

Token `.dark` disinkronkan agar tidak ada variabel yang hilang, **tanpa** verifikasi
kontras (kode mati, §3.5).

### 4.4 Pemetaan utilitas Tailwind (WAJIB, satu commit dengan §4.3)

**Jebakan Tailwind v3:** mendeklarasikan variabel CSS baru di `app.css` **tidak**
menghasilkan utilitas apa pun. `tailwind.config.js` menyebut `colors` secara
eksplisit satu per satu (`background`, `card`, `primary`, `secondary`, `muted`,
`accent`, `destructive`, `border`, `input`, `ring`, `chart-1..5`). Tanpa penambahan
di config, kelas seperti `bg-primary-soft`, `text-success`, atau `bg-warning` akan
**gagal senyap** — tidak ada error, kelasnya hanya tidak dirender.

`theme.extend.colors` harus ditambah, **dalam commit yang sama** dengan variabel CSS:

```js
'primary-soft':  'hsl(var(--primary-soft))',
'primary-strong':'hsl(var(--primary-strong))',
success: { DEFAULT: 'hsl(var(--success))',
           foreground: 'hsl(var(--success-foreground))',
           soft: 'hsl(var(--success-soft))' },
warning: { /* idem */ },
info:    { /* idem */ },
// chart-1..5 sudah ada di config dan tetap dipertahankan;
// yang berubah hanya nilainya di app.css (§4.3).
```

Catatan: `chart-3..5` sudah terpetakan di config, jadi yang perlu diperbaiki untuk
chart hanyalah nilai variabelnya — bukan pemetaannya.

### 4.5 Tipografi & skala

| Peran | Kelas | Catatan |
|---|---|---|
| Page title | `text-2xl md:text-3xl font-extrabold tracking-tight` | dulu `text-xl font-bold` |
| Section | `text-base font-bold` | |
| Statistik | `text-3xl md:text-4xl font-extrabold tabular-nums` | `tabular-nums` mencegah angka bergoyang |
| Body | `text-sm` | |
| Meta | `text-xs text-muted-foreground` | |

### 4.6 Motion

Keyframes `rise` (translateY 8px + opacity), `pop` (scale 0.96→1), `grow` (scaleY 0→1
untuk bar chart). Durasi 150–250 ms, easing `cubic-bezier(0.16, 1, 0.3, 1)`. Semua
dibungkus `motion-safe:`; `@media (prefers-reduced-motion: reduce)` mematikan seluruh
animasi dan transisi.

## 5. Layout & Navigasi

### 5.1 Desktop (≥768px)

Sidebar `w-64` tetap: ikon Lucide per item, indikator aktif berupa bar aksen kiri +
latar `--primary-soft`, chip komunitas (logo + nama), kartu pengguna (avatar inisial,
nama, badge peran) di bagian bawah.

### 5.2 Mobile (<768px) — `BottomNav.vue`

Fixed bottom, `pb-[env(safe-area-inset-bottom)]`, target sentuh ≥44 px, ikon + label
10 px, item aktif berwarna `--primary`.

| Peran | Item |
|---|---|
| `anggota` | Dashboard · Laporan · **Tambah** (FAB tengah) · Profil |
| `admin` | Dashboard · Laporan · **Tambah** · Kategori · Pengguna |

**Kriteria penerimaan wajib:**
1. `padding-bottom: env(safe-area-inset-bottom)` pada bar.
2. `<main>` mendapat `pb-24 md:pb-6` sehingga konten tidak tertutup bar.
3. Tab khusus admin **disembunyikan** untuk `anggota` — grid 3 vs 5 item, bukan sel
   kosong.
4. Halaman Kategori & Pengguna **dapat dijangkau** dari ponsel oleh admin
   (memperbaiki §3.4).

Header mobile: judul + tombol menu/avatar (nama, peran, Keluar) menggantikan tiga
tautan teks. Ditambah skip-link "Lewati ke konten" dan `focus-visible:ring-2
ring-offset-2` yang konsisten di semua elemen interaktif.

## 6. Komponen

| Komponen | Status | Isi |
|---|---|---|
| `PageHeader.vue` | baru | Judul, subjudul, slot aksi — dipakai semua halaman |
| `StatCard.vue` | tulis ulang | Chip ikon Lucide, angka `tabular-nums`, badge delta vs bulan lalu, prop `tone` (primary/success/warning/info) |
| `charts/MonthlyBar.vue` | tulis ulang | **SVG tetap**; tambah label sumbu X/Y, teks nilai, tooltip HTML saat hover **dan** focus keyboard, penanda bulan berjalan, animasi `grow`, tabel `sr-only` sebagai fallback a11y |
| `charts/CategoryDonut.vue` | tulis ulang | Palet dari `--chart-*` (hapus 3 hex hardcoded, perbaiki §3.2), legenda + persen + jumlah, hover irisan, total di tengah |
| `ActivityPanel.vue` | baru | Hari ini / minggu ini / bulan ini + streak (angka + ikon, **tanpa** progress bar). **Tidak** memuat linimasa laporan terbaru — daftar itu milik bloknya sendiri (§7.3) agar tidak dirender dua kali |
| `ReportCard.vue` | baru | Kartu mobile: badge kategori, judul, lokasi, tanggal, pelapor |
| `BottomNav.vue`, `NavItem.vue` | baru | Navigasi mobile (§5.2) |
| `EmptyState.vue` | tulis ulang | Ikon, judul, deskripsi, aksi; varian per konteks |
| `Skeleton.vue` | baru | Placeholder saat memuat |
| `PhotoUploader.vue` | tulis ulang | Dropzone drag-and-drop, hitungan `n/10`, validasi tipe/ukuran per file, tombol hapus pakai ikon Lucide (mengganti glyph `✕`) |
| `ui/badge` | perluas | Varian `success`/`warning`/`info` + gaya pill |

**Aturan lokasi & impor (wajib, berlaku untuk SEMUA komponen baru di atas):**

- Setiap komponen baru/komponen yang ditulis ulang diletakkan di
  **`resources/js/components/`** (huruf kecil) — termasuk `charts/*`.
- Setiap impor baru memakai **`@/components/`**, bukan `@/Components/`.
- Perbaikan §3.6 hanya menjangkau 5 impor yang sudah ada; tanpa aturan ini,
  `@/Components/…` akan **muncul kembali** bersama setiap file baru dan bug build
  Linux kembali tanpa terasa. Karena itu pemeriksaan
  `grep -rn "@/Components/" resources/js` → **0 hasil** dilakukan **per halaman**
  (§9), bukan hanya sekali di akhir.

## 7. Halaman

1. **Landing** — hero gradient bertoken, wordmark besar, dua kartu komunitas
   (logo, jumlah anggota & laporan), hover lift, animasi masuk berurutan.
2. **Login** — panel dua kolom: kiri gradient + logo + nama komunitas, kanan form;
   target sentuh diperbesar, ringkasan error di atas form, `autofocus` +
   `autocomplete` yang benar.
3. **Dashboard** — hero sapaan (ikon, bukan emoji) + tanggal Indonesia + chip
   komunitas; pemilih tahun jadi segmented control; 4 stat card ber-ikon + tren;
   baris chart (bar + donut); `ActivityPanel` (angka aktivitas + streak saja); lalu
   blok **"5 laporan terbaru"** sebagai baris teks sederhana — blok ini berdiri
   sendiri dan **tidak** berada di dalam `ActivityPanel` (§6).
4. **Laporan Index** — `PageHeader` + aksi; filter dilipat di mobile (chip ringkas),
   selalu terbuka di desktop; **tabel ≥768px, kartu <768px** dari satu markup;
   paginasi jadi `<Link>` sungguhan (perbaiki §3.7); hitungan hasil.
5. **Laporan Show** — hero judul + meta (kategori, tanggal, lokasi, pelapor), galeri
   foto grid + lightbox yang sudah ada (dirapikan), kartu deskripsi, sidebar meta di
   desktop.
6. **Laporan Form** — tiga seksi bernomor (Informasi / Waktu & Lokasi / Dokumentasi),
   penghitung karakter, action bar lengket di bawah pada mobile.
7. **Kategori & Pengguna** — tabel di desktop, kartu di mobile; dialog dirapikan
   (validasi inline, tombol ikon, `AlertDialog` untuk konfirmasi hapus); pengguna
   pakai avatar inisial + badge peran.

## 8. Perubahan Backend (minimal, tanpa migrasi)

Hanya `DashboardController` dan route `/`. Tidak ada tabel/kolom baru.

```php
'stats' => [
    'total_reports', 'total_members', 'this_month', 'this_year',
    'prev_month',   // BARU — laporan bulan lalu, untuk badge delta
],
'activity' => [      // BARU — dihitung dari reports.start_date
    'today', 'week', 'this_month', 'streak',
],
'recent' => [        // TIDAK berubah + created_ago
    'id', 'title', 'category_name', 'start_date', 'user_name', 'created_ago',
],
```

Definisi `streak`: jumlah bulan berurutan yang memiliki ≥1 laporan, dihitung mundur
dari bulan berjalan; satu bulan kosong memutus rentetan.

Optimasi: 12 kueri `$monthly` diganti satu
`SELECT MONTH(start_date) AS m, COUNT(*) FROM reports WHERE community = ? AND
YEAR(start_date) = ? GROUP BY m`, lalu diisi ke 12 slot (bulan tanpa data = 0).

Route `/` (sekarang `Route::inertia('/')` tanpa data) menjadi closure yang mengirim
`counts` per komunitas (anggota + laporan) untuk kartu Landing — 2 kueri agregat.

Kontrak test: `DashboardTest` mengunci `stats.total_reports`, `stats.total_members`,
`stats.this_year`, `monthly` (12 entri), dan `recent` (3 entri). Semua tetap
terpenuhi karena perubahan hanya **menambah** kunci. `HealthTest`/`AuthTest` mengunci
`component('Landing')` dan `community: null` — juga tetap terpenuhi.

## 9. Verifikasi

1. 45 test PHP tetap hijau (`php artisan test`).
2. Test baru **hanya** untuk perhitungan `streak` (logika yang benar-benar bisa salah:
   batas bulan, bulan berjalan, bulan kosong di tengah). Ditulis merah dulu.
3. `npm run build` sukses — sekaligus membuktikan perbaikan casing §3.6.
   **Selain itu, `grep -rn "@/Components/" resources/js` → 0 hasil diperiksa per
   halaman** setelah tiap halaman selesai, bukan hanya di akhir, agar impor
   kapitalisasi tidak menyelinap masuk bersama komponen baru (§6).
4. Pemeriksaan nyata di browser pada 375 / 768 / 1024 / 1440 px: tidak ada overflow
   horizontal, bottom nav tidak menutupi konten, urutan Tab logis, kontras teks,
   lightbox, dan upload foto.
5. Bukti diambil sebagai pengukuran DOM (geometri, warna hasil komputasi, urutan
   fokus) — **bukan** penilaian visual, karena model tidak dapat melihat gambar.
   Screenshot disertakan sebagai artefak untuk ditinjau user.

## 10. Risiko

| Risiko | Mitigasi |
|---|---|
| Cakupan besar: 7 halaman + ~12 komponen | Fondasi token & komponen dulu, baru halaman; tiap halaman diverifikasi sebelum lanjut |
| Token baru bisa lolos dari tema komunitas | Nilai kontras dihitung (§4.3); chart-3..5 kini di-override per komunitas |
| Regresi pada test yang ada | Hanya penambahan kunci props; tidak ada penghapusan |
| Kapitalisasi direktori hanya muncul saat build | `npm run build` masuk daftar verifikasi wajib (§9.3) |
| Model tidak dapat menilai estetika secara visual | Keterbatasan dinyatakan eksplisit; screenshot diserahkan ke user untuk penilaian akhir |

## 11. Supersesi & Catatan Drift Dokumen Lama

Spec 2026-09-10 (`2026-09-10-lafagen-design.md`) adalah dokumen yang **disetujui user**
dan tetap berlaku untuk sistem fungsional (skema data, otorisasi, isolasi komunitas).
Namun pada dua titik implementasi menyimpang darinya, dan spec ini secara eksplisit
**menggantikan** bagian tersebut agar kedua dokumen tidak saling bertentangan:

| Bagian spec lama | Isi lama | Kenyataan implementasi | Status |
|---|---|---|---|
| §2 baris "Grafik" | `recharts` via chart wrapper shadcn-vue | `package.json` tidak memuat `recharts`; chart adalah SVG tulis-tangan di `Components/charts/` | **Digantikan** — §2 spec ini: tetap SVG, tanpa pustaka baru |
| §2 baris "Frontend" | "Inertia.js v1" | `composer.json` memakai `inertiajs/inertia-laravel ^2.0` | **Digantikan** — spec ini mengikuti v2 |
| §4 (tema via CSS variables) | tema lewat `data-community` | benar dan dipertahankan | **Diperluas** — §4.4 menambah pemetaan `colors` di Tailwind yang belum ada |

Perbaikan kapitalisasi impor (§3.6) juga dicatat di sini agar dapat dilacak: ia bukan
keputusan desain baru, melainkan bug yang belum terdeteksi sampai build Linux.

Dokumen lama tidak diubah di luar cakupan ini.
