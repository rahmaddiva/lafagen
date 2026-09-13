# Lafagen — Rencana Implementasi Desain Ulang UI/UX

> **Untuk pelaksana:** Rencana ini ditulis untuk dijalankan tugas-per-tugas. Setiap
> tugas mandiri, punya kriteria penerimaan yang bisa diamati, dan diakhiri commit.
> Jangan mengerjakan dua tugas sekaligus.

**Sasaran:** Menerapkan desain "Energik Sporty" ke seluruh halaman Lafagen, plus
memperbaiki 8 temuan audit, tanpa mengubah alur bisnis, skema data, atau otorisasi.

**Spec:** `docs/superpowers/specs/2026-09-13-lafagen-ui-ux-design.md`
**Spec sebelumnya (masih berlaku untuk fungsional):** `docs/superpowers/specs/2026-09-10-lafagen-design.md`
**Baseline:** 45 test hijau, 247 assertion (terverifikasi 2026-09-13).

**Aturan global (berlaku di semua tugas):**

1. Komponen baru ditulis ke `resources/js/components/` (huruf kecil); semua impor
   memakai `@/components/`. **Dilarang** menulis `@/Components/`.
2. Setiap tugas yang menyentuh UI diakhiri dengan
   `grep -rn "@/Components/" resources/js` → **0 hasil**.
3. Setiap tugas yang menyentuh backend diakhiri dengan `php artisan test` tetap
   **45+ test hijau**.
4. Tidak ada penambahan dependensi npm/composer.
5. Tidak menyentuh `docs/superpowers/plans/2026-09-10-lafagen-implementation.md`.

---

## Tugas 1: Token, tipografi, dan pemetaan Tailwind

**Termasuk:** §4.1, §4.2, §4.3, §4.4, §4.6 spec; temuan §3.2 dan §3.3.

**Files:**
- Modify: `resources/css/app.css`
- Modify: `tailwind.config.js`
- Modify: `resources/views/app.blade.php`

**Langkah:**

1. **Tailwind config — fontFamily.** Ganti `Figtree` pada `fontFamily.sans` dengan
   `'"Plus Jakarta Sans"', 'Figtree', ...defaultTheme.fontFamily.sans`, dan tambah
   `display` dengan nilai yang sama.

2. **Tailwind config — colors (KRITIS).** `colors` di config ini disebut satu per
   satu; variabel CSS baru **tidak** otomatis menghasilkan utilitas. Tambahkan:

   ```js
   'primary-soft': 'hsl(var(--primary-soft))',
   'primary-strong': 'hsl(var(--primary-strong))',
   success: { DEFAULT: 'hsl(var(--success))', foreground: 'hsl(var(--success-foreground))', soft: 'hsl(var(--success-soft))' },
   warning: { DEFAULT: 'hsl(var(--warning))', foreground: 'hsl(var(--warning-foreground))', soft: 'hsl(var(--warning-soft))' },
   info:    { DEFAULT: 'hsl(var(--info))',    foreground: 'hsl(var(--info-foreground))',    soft: 'hsl(var(--info-soft))' },
   ```

   `chart-1..5` **sudah** terpetakan — jangan diubah pemetaannya.

3. **Tailwind config — radius & shadow.** `--radius` di app.css → `0.75rem`.
   Tambah `boxShadow.card` dan `boxShadow.lift` (§4.2 spec).

4. **app.css — JANGAN ubah `--primary`/`--accent` komunitas.** Nilai yang ada
   (`fad: 166 75% 28%`, `genre: 205 90% 40%`) sudah lolos WCAG AA (5,07:1 dan
   4,84:1). Spec awal saya sempat salah menyebut nilai lain; yang benar adalah nilai
   di repo. Perubahan bersifat **aditif**. **Pakai nilai literal berikut apa adanya**
   — semua sudah dihitung rasio kontrasnya:

   **a. `--primary-soft` / `--primary-strong`** (ditambahkan ke blok komunitas yang ada):

   | Komunitas | `--primary-soft` | `--primary-strong` |
   |---|---|---|
   | `[data-community='fad']` | `162 73% 92%` | `166 75% 18%` |
   | `[data-community='genre']` | `204 94% 93%` | `205 90% 30%` |

   Kontras `primary-strong` di atas `primary-soft`: FAD **8,44:1**, GENRE **6,41:1**.

   **b. `--chart-3/4/5` per komunitas** — ini yang **memperbaiki §3.2**. Nilai
   `--chart-1` dan `--chart-2` sudah ada di repo; **biarkan**, hanya tambahkan 3–5:

   | Token | FAD (teal family) | GENRE (blue family) |
   |---|---|---|
   | `--chart-3` | `168 66% 27%` | `212 80% 33%` |
   | `--chart-4` | `172 55% 59%` | `198 84% 68%` |
   | `--chart-5` | `150 48% 46%` | `222 65% 58%` |

   (FAD `--chart-1` tetap `166 75% 35%`, `--chart-2` tetap `160 60% 45%`.
   GENRE `--chart-1` tetap `205 90% 45%`, `--chart-2` tetap `199 89% 55%`.)

   **c. Blok `public` baru** — memperbaiki §3.3 (Landing kini bertoken):

   ```css
   [data-community='public'] {
     --primary: 240 5.9% 10%;
     --primary-foreground: 0 0% 98%;
     --ring: 240 5.9% 10%;
     --primary-soft: 240 4.8% 95.9%;
     --primary-strong: 240 5.9% 10%;
     --accent: 240 4.8% 95.9%;
     --accent-foreground: 240 5.9% 10%;
     --chart-1: 240 6% 26%;
     --chart-2: 240 5% 40%;
     --chart-3: 240 4% 52%;
     --chart-4: 240 5% 64%;
     --chart-5: 240 6% 76%;
   }
   ```

   **d. Token status di `:root`** (dipakai badge/delta; nilai literal, semua lolos).
   **Perhatikan konvensi penamaan:** `-foreground` berarti **teks di atas DEFAULT**
   (sama seperti `primary-foreground`/`destructive-foreground`), sedangkan teks di
   atas `-soft` memakai **`-strong`**:

   ```css
   --success: 142 71% 30%;          /* 4,83:1 vs putih */
   --success-foreground: 0 0% 100%; /* teks putih DI ATAS success */
   --success-soft: 138 76% 95%;
   --success-strong: 142 72% 20%;   /* teks DI ATAS success-soft: 8,02:1 */
   --warning: 32 95% 33%;           /* 5,24:1 vs putih */
   --warning-foreground: 0 0% 100%;
   --warning-soft: 48 96% 92%;
   --warning-strong: 32 95% 22%;    /* di atas soft: 8,43:1 */
   --info: 217 91% 42%;             /* 6,64:1 vs putih */
   --info-foreground: 0 0% 100%;
   --info-soft: 214 95% 94%;
   --info-strong: 217 91% 28%;      /* di atas soft: 9,26:1 */
   ```

   **Jangan** memakai `text-success-foreground` di atas `bg-success` dengan maksud
   "teks gelap di atas hijau" — bentuk lama itu menghasilkan **1,78:1** (gagal
   total). Pola yang benar:
   `bg-success text-success-foreground` (putih di hijau) **atau**
   `bg-success-soft text-success-strong` (hijau tua di tint). Idem warning/info.

   **Catatan penting:** `success` pada `142 71% 33%` dan `warning` pada `38 92% 34%`
   **GAGAL** AA (4,11:1 dan 4,40:1). Jangan "membulatkan" nilai di atas ke angka
   yang lebih terang.

   Sinkronkan seluruh token baru ke blok `.dark` (tanpa verifikasi kontras).

5. **app.css — motion.** Keyframes `rise`, `pop`, `grow` di dalam
   `@media (prefers-reduced-motion: no-preference)`; tambah blok
   `@media (prefers-reduced-motion: reduce)` yang mematikan animasi & transisi.

6. **app.blade.php — font.** Tambah `preconnect` + stylesheet Plus Jakarta Sans
   (400;500;600;700;800, `display=swap`) di `<head>`, **sebelum** `@vite`.

**Verifikasi:**
- `grep -n "primary-soft\|success\|warning\|info" tailwind.config.js` → ada.
- `npm run build` sukses.
- Dev server jalan; probe browser: `getComputedStyle(document.body).fontFamily`
  mengandung `Plus Jakarta Sans`, dan `document.fonts.check('16px "Plus Jakarta Sans"')`
  → `true` (membuktikan font benar-benar termuat, bukan hanya dideklarasikan).
- Probe di dev server (bukan asumsi):
  ```js
  const cs = getComputedStyle(document.documentElement);
  // di /fad/dashboard
  cs.getPropertyValue('--chart-3').trim();  // harus "168 66% 27%"
  cs.getPropertyValue('--chart-4').trim();  // harus "172 55% 59%"
  cs.getPropertyValue('--chart-5').trim();  // harus "150 48% 46%"
  // di /genre/dashboard
  cs.getPropertyValue('--chart-3').trim();  // harus "212 80% 33%"
  ```
  Nilai `197 37% 24%` (bawaan lama) **tidak boleh** muncul di kedua komunitas.
- Probe `--primary` di `/fad/dashboard` harus tetap `166 75% 28%` (membuktikan
  palet lama tidak dirusak), dan di `/` harus terdefinisi (blok `public` ada).

---

## Tugas 2: Perbaikan casing impor (prasyarat build)

**Termasuk:** temuan §3.6. Harus dikerjakan sebelum tugas UI lain agar build Linux
tidak pecah.

**Files:**
- Modify: `resources/js/Pages/Dashboard.vue` (baris 9, 10, 11)
- Modify: `resources/js/Pages/Reports/Form.vue` (baris 11)
- Modify: `resources/js/Pages/Reports/Index.vue` (baris 15)

**Langkah:** Ganti 5 specifier `@/Components/…` menjadi `@/components/…`. **Jangan**
menyentuh `@/Layouts/…` atau `@/Pages/…` — direktori itu memang berhuruf besar di
disk dan impornya sudah benar. Tidak ada direktori yang di-rename.

**Verifikasi:**
- `grep -rn "@/Components/" resources/js` → **0 hasil** (peka huruf besar/kecil).
- `npm run build` sukses.

---

## Tugas 3: Komponen dasar bersama

**Files:**
- Create: `resources/js/components/PageHeader.vue`
- Create: `resources/js/components/Skeleton.vue`
- Rewrite: `resources/js/components/EmptyState.vue`
- Modify: `resources/js/components/ui/badge/Badge.vue` (+ varian success/warning/info, gaya pill)

**Kontrak `PageHeader.vue`** (dipakai Tugas 5–7):
```
props: { title: String (wajib), subtitle: String (opsional) }
slots: { actions }
```
Merender `text-2xl md:text-3xl font-extrabold tracking-tight` untuk judul.

**Kontrak `EmptyState.vue`** — **kontrak lama harus dipertahankan**; ia sudah dipakai
`Reports/Index.vue:122-128` dengan `title`, `description`, `action-label`,
`:action-href`. Menambah `icon` boleh (aditif), tetapi **jangan** mengganti props
berbasis atribut menjadi slot, karena akan memecahkan pemakaian yang ada:

```
props: {
  title:       String, default 'Belum ada data',
  description: String, default '',
  actionLabel: String, default '',
  actionHref:  String, default '',
  icon:        String, default '',   // BARU, opsional
}
```
(Vue: `action-label`/`:action-href` memetakan ke `actionLabel`/`actionHref`.)

**Kontrak `Skeleton.vue`:**
```
props: { variant: 'text' | 'card' | 'chart', count: Number (default 1) }
```

**Verifikasi:**
- `npm run build` sukses; `grep -rn "@/Components/" resources/js` → 0 hasil.
- Halaman Laporan masih tampil tanpa error (EmptyState dipakai saat filter kosong).

---

## Tugas 4: Perbaikan backend dashboard + route landing

**Termasuk:** §8 spec; temuan §3.8. **Tanpa migrasi.**

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/StreakTest.php`

**Langkah:**

1. **Test dulu (merah).** Tulis `StreakTest.php` untuk logika `streak`:
   - 3 bulan berurutan termasuk bulan berjalan → `3`.
   - Bulan lalu & bulan ini → `2`.
   - Bulan berjalan kosong → `0`.
   - Bulan kosong di tengah memutus rentetan (mis. bulan -1 dan -3 → `1`).
   Jalankan `php artisan test --filter=StreakTest` → **FAIL**.

2. **`$monthly` — ganti 12 kueri jadi satu.** Ganti loop `for ($m = 1; $m <= 12; $m++)`
   dengan satu query agregat, lalu isi 12 slot (bulan tanpa data = 0). Bentuk output
   **harus tetap** `[{month, label, total}]` × 12 karena `DashboardTest` mengunci
   `monthly.5.total`.

   **Batasan portabilitas SQL (penting):** `phpunit.xml:30-31` memaksa
   `DB_CONNECTION=sqlite` (`force="true"`), sedangkan dev/prod MySQL.
   **SQLite tidak punya `MONTH()`**, dan MySQL dev berjalan dengan
   `ONLY_FULL_GROUP_BY`. Solusi yang dipakai dan sudah terbukti di kedua mesin:
   pilih ekspresi berdasarkan driver, lalu tetap `GROUP BY` ekspresi yang sama —

   ```php
   $monthExpr = match (DB::connection()->getDriverName()) {
       'sqlite' => "cast(strftime('%m', start_date) as integer)",
       'pgsql'  => 'extract(month from start_date)::integer',
       default  => 'month(start_date)',
   };
   ```

   **Jangan** menyelesaikan test merah dengan mengganti koneksi DB atau menghapus
   assertion. Untuk `today`/`week`/`this_month`, pakai `whereDate`/`whereBetween`
   (bukan `DB::raw` fungsi tanggal) agar driver-safe.

3. **Tambah `stats.prev_month`** — jumlah laporan bulan lalu. Hanya menambah kunci.

4. **Tambah `activity`** = `{today, week, this_month, streak}`, dihitung dari
   `reports.start_date` komunitas terkait. Definisikan `streak` sebagai method
   privat terpisah agar bisa diuji lewat test (bila perlu, jadikan method publik
   statis di controller atau helper yang bisa dipanggil test).

5. **Tambah `recent[].created_ago`** (mis. `"3 hari lalu"`). **Jangan** tambah
   `photo_url` — kartu thumbnail tidak dipilih user.

6. **Route `/`** — ganti `Route::inertia('/')` menjadi closure yang mengirim
   `counts` per komunitas (2 query agregat: jumlah anggota + jumlah laporan) dan
   tetap merender komponen `Landing`.

**Verifikasi:**
- `php artisan test --filter=StreakTest` → **PASS**.
- `php artisan test` → **45 test lama + test baru, semua hijau**.
- `DashboardTest` khususnya: `stats.total_reports`, `stats.total_members`,
  `stats.this_year`, `monthly` 12 entri, `monthly.5.total`, `recent` 3 entri.
- Query count: buktikan `$monthly` tidak lagi 12 kueri (mis. `DB::listen` atau
  `assertQueryCount`-manual) — kueri untuk blok bulan harus **1**, bukan 12.

---

## Tugas 5: Layout, navigasi mobile, dan Landing

**Termasuk:** §5 spec; temuan §3.4 (bug akses: admin tak bisa buka Kategori/Pengguna
dari HP).

**Files:**
- Modify: `resources/js/Layouts/CommunityLayout.vue`
- Create: `resources/js/components/BottomNav.vue`
- Create: `resources/js/components/NavItem.vue`
- Modify: `resources/js/Pages/Landing.vue`

**Langkah:**

1. **`NavItem.vue`** — satu item nav (ikon Lucide, label, status aktif, target
   sentuh ≥44px). Dipakai sidebar **dan** bottom nav (satu implementasi, dua tempat).

2. **`BottomNav.vue`** — fixed bottom. **Kriteria penerimaan wajib:**
   - `padding-bottom: env(safe-area-inset-bottom)`.
   - `anggota` → 4 item (Dashboard · Laporan · Tambah · Profil);
     `admin` → 5 item (+ Kategori, Pengguna). Item admin **disembunyikan** untuk
     anggota — grid 3 vs 5, bukan sel kosong.
   - Item aktif berwarna `--primary`.

3. **`CommunityLayout.vue`** — sidebar desktop: ikon per item, indikator aktif bar
   aksen kiri + latar `--primary-soft`, chip komunitas (logo+nama), kartu pengguna
   (avatar inisial, nama, badge peran). Tambah `BottomNav` untuk mobile. `<main>`
   mendapat `pb-24 md:pb-6`. Ganti tiga tautan teks header mobile dengan
   judul + menu avatar (nama, peran, Keluar). Tambah skip-link "Lewati ke konten"
   dan `focus-visible:ring-2 ring-offset-2` yang konsisten.

4. **`Landing.vue`** — pakai token (kini `public` sudah ada), hero gradient,
   wordmark besar, dua kartu komunitas dengan `counts` dari Tugas 4, hover lift,
   animasi masuk berurutan.

**Verifikasi (browser, bukan asumsi):**

- **Tema saat navigasi SPA (§3.5b) — wajib, dan `tab.goto()` TIDAK bisa dipakai:**
  sebagai tamu, buka `/` lalu klik kartu komunitas sebagai `<Link>` Inertia:
  ```js
  await tab.click('a[href="/fad/login"]');
  // harus: dataset.community === 'fad' dan --primary === '166 75% 28%'
  await tab.click('a[href="/genre/login"]');   // dari / lagi
  // harus: dataset.community === 'genre' dan --primary === '205 90% 40%'
  ```
  Full page load (`tab.goto`) merender ulang blade dan **selalu** lolos, sehingga
  tidak mendeteksi bug ini sama sekali.
- 375px: `BottomNav` terlihat; `<main>` **tidak** tertutup (ukur
  `getBoundingClientRect()` elemen terakhir vs offset bar).
- Login sebagai `admin`, 375px: **halaman Kategori dan Pengguna dapat dibuka** —
  ini pembuktian utama §3.4.
- Login sebagai `anggota`, 375px: item Kategori/Pengguna **tidak ada** di DOM.
- 768px+: sidebar terlihat, bottom nav tersembunyi.
- `/` menunjukkan jumlah anggota/laporan yang benar per komunitas.
- Tab order logis dari atas; skip-link muncul saat difokus.
- `grep -rn "@/Components/" resources/js` → 0 hasil.

---

## Tugas 6: Dashboard

**Termasuk:** §7.3 spec.

**Files:**
- Rewrite: `resources/js/Pages/Dashboard.vue`
- Rewrite: `resources/js/components/StatCard.vue`
- Create: `resources/js/components/ActivityPanel.vue`
- Rewrite: `resources/js/components/charts/MonthlyBar.vue`
- Rewrite: `resources/js/components/charts/CategoryDonut.vue`

**Langkah:**

1. **`MonthlyBar.vue`** — tetap SVG tulis-tangan, **tanpa pustaka baru**. Tambah
   label sumbu X/Y, teks nilai, tooltip HTML saat hover **dan** focus keyboard,
   penanda bulan berjalan, animasi `grow`, dan tabel `sr-only` sebagai fallback a11y.

2. **`CategoryDonut.vue`** — palet dari `--chart-*` (hapus 3 hex hardcoded
   `#65a30d/#ca8a04/#dc2626`), legenda + persen + jumlah, hover irisan, total di
   tengah.

3. **`StatCard.vue`** — chip ikon Lucide, angka `tabular-nums`, badge delta vs
   bulan lalu (pakai `stats.prev_month`), prop `tone` (primary/success/warning/info).

4. **`ActivityPanel.vue`** — **hanya** angka: hari ini / minggu ini / bulan ini +
   streak (angka + ikon, **tanpa** progress bar). **Tidak** memuat daftar laporan
   terbaru.

5. **`Dashboard.vue`** — hero sapaan (ikon, bukan emoji) + tanggal Indonesia + chip
   komunitas; pemilih tahun jadi segmented control; 4 stat card; baris chart
   (MonthlyBar + CategoryDonut); `ActivityPanel`; lalu blok **terpisah** "5 laporan
   terbaru" sebagai **baris teks sederhana** dengan `created_ago`.

**Verifikasi:**
- `grep -rn "@/Components/" resources/js` → 0 hasil; `npm run build` sukses.
- 375/768/1024/1440px: tidak ada overflow horizontal
  (`document.documentElement.scrollWidth <= innerWidth`).
- Donut (membuktikan §3.2 diperbaiki): baca `fill` tiap irisan dan bandingkan
  dengan token terkomputasi:
  ```js
  const cs = getComputedStyle(document.documentElement);
  const expect = [1,2,3,4,5].map(i => `hsl(${cs.getPropertyValue('--chart-'+i).trim()})`);
  const fills = [...document.querySelectorAll('[data-donut-slice]')].map(e => e.getAttribute('fill'));
  // fills[0..4] harus berurutan sama dengan expect, dan
  // TIDAK boleh ada '197 37% 24%' | '43 74% 66%' | '27 87% 67%'
  // juga TIDAK boleh ada hex hardcoded lama: #65a30d | #ca8a04 | #dc2626
  ```
  Di `/fad/dashboard` warna irisan ke-3 harus `168 66% 27%`; di `/genre/dashboard`
  harus `212 80% 33%`. Tambahkan `data-donut-slice` pada `<path>` donut agar
  pemeriksaan ini mungkin.
- Tooltip bar chart muncul saat **keyboard** fokus, bukan hanya hover.
- Daftar "5 laporan terbaru" muncul **tepat satu kali** di DOM (membuktikan
  kontradiksi §6/§7.3 benar-benar terselesaikan).

---

## Tugas 7: Halaman Laporan, Kategori, Pengguna

**Termasuk:** §7.4–7.7 spec; temuan §3.7.

**Files:**
- Modify: `resources/js/Pages/Reports/Index.vue`
- Modify: `resources/js/Pages/Reports/Show.vue`
- Modify: `resources/js/Pages/Reports/Form.vue`
- Modify: `resources/js/Pages/Categories/Index.vue`
- Modify: `resources/js/Pages/Users/Index.vue`
- Rewrite: `resources/js/components/PhotoUploader.vue`
- Create: `resources/js/components/ReportCard.vue`

**Langkah:**

1. **`Reports/Index.vue`** — `PageHeader` + aksi; filter dilipat di mobile (chip
   ringkas), selalu terbuka di desktop; **tabel ≥768px, kartu <768px** dari satu
   markup; hitungan hasil.
   **§3.7:** paginasi jadi `<Link>` sungguhan — hapus `<Button @click="router.get">`
   dan `v-html` mentah pada label.

2. **`ReportCard.vue`** — kartu mobile: badge kategori, judul, lokasi, tanggal,
   pelapor.

3. **`Reports/Show.vue`** — hero judul + meta, galeri foto + lightbox (dirapikan),
   kartu deskripsi, sidebar meta di desktop.

4. **`Reports/Form.vue`** — tiga seksi bernomor; penghitung karakter; action bar
   lengket di bawah pada mobile.

5. **`PhotoUploader.vue`** — dropzone drag-and-drop, hitungan `n/10`, validasi
   tipe/ukuran per file, tombol hapus pakai ikon Lucide (**mengganti glyph `✕`**).

6. **Categories & Users Index** — tabel desktop, kartu mobile; validasi inline,
   tombol ikon, `AlertDialog` untuk konfirmasi hapus; pengguna pakai avatar inisial
   + badge peran.

**Verifikasi:**
- Paginasi: elemen adalah `<a>` dengan `href` benar — buktikan dengan
  `document.querySelectorAll('nav[role=navigation] a').length`, dan bahwa klik
  tengah/Ctrl+klik membuka tab baru.
- 375px: Index memakai kartu; 768px+: memakai tabel.
- Upload: file >5MB dan tipe salah ditolak dengan pesan Indonesia; hitungan `n/10`
  benar saat tambah **dan** hapus.
- Form: `php artisan test` tetap hijau (kontrak backend tidak berubah).
- `grep -rn "@/Components/" resources/js` → 0 hasil; `npm run build` sukses.

---

## Tugas 8: Verifikasi akhir

**Langkah:**

1. `php artisan test` → **semua hijau** (45 lama + test baru dari Tugas 4).
2. `npm run build` → sukses (sekaligus bukti perbaikan casing).
3. `grep -rn "@/Components/" resources/js` → **0 hasil**.
4. Pemeriksaan browser 375 / 768 / 1024 / 1440 px pada **keseluruhan** halaman:
   Landing, Login, Dashboard, Laporan Index/Show/Form, Kategori, Pengguna — untuk
   **FAD dan GENRE** (10 halaman × 4 lebar). Catat: overflow horizontal, urutan Tab,
   bottom nav tidak menutupi konten, kontras teks, lightbox, upload foto.
5. Screenshot tiap halaman pada 375px dan 1440px sebagai artefak untuk user.

**Batas yang dinyatakan jujur:** penilaian **estetika** akhir bukan oleh model —
model tidak dapat melihat gambar, sehingga bukti yang diberikan berupa pengukuran
DOM (geometri, warna hasil komputasi, rasio kontras, urutan fokus). Screenshot
diserahkan ke user untuk penilaian visual.

---

## Ringkasan Perintah Verifikasi

```bash
php artisan test                                   # 45+ hijau
npm run build                                      # sukses (bukti casing)
grep -rn "@/Components/" resources/js              # 0 hasil
```

## Yang TIDAK dikerjakan (sesuai keputusan user)

- ❌ Goal/progress bulan ini (tidak dipilih) — tidak ada progress bar, tidak ada config target.
- ❌ Laporan terbaru jadi kartu + thumbnail (tidak dipilih) — tetap baris teks; tidak ada `photo_url`.
- ❌ Verifikasi kontras dark mode — `.dark` tidak dapat diaktifkan; token disinkronkan saja.
- ❌ Pustaka grafik baru (recharts/unovis) — chart tetap SVG tulis-tangan.
- ❌ Migrasi database, perubahan otorisasi, perubahan skema.
- ❌ Rename direktori `components/` menjadi `Components/`.
