# Lafagen — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Aplikasi web laporan proker dua komunitas (FAD & GENRE) Kab. Tanah Laut — satu codebase, dua tema/prefix, CRUD laporan + foto, dashboard grafik, manajemen user/kategori, export Excel.

**Architecture:** Laravel 11 monolith + Inertia.js (inertia-laravel ^2) client-side render + Vue 3 SFC `<script setup>`. Isolasi komunitas dua lapis: path prefix `/fad`, `/genre` (middleware) + kolom `community` di semua tabel bisnis (scope eksplisit tiap query). Tema per komunitas via atribut `data-community` pada `<html>` + override CSS variables shadcn-vue.

**Tech Stack:** PHP ≥8.2 (target 8.3), Laravel ^11, inertia-laravel ^2, Vue 3, Vite (bawaan skeleton), Tailwind 3.4 + shadcn-vue, recharts (via chart wrapper shadcn-vue), MySQL 8 dev / SQLite in-memory test, maatwebsite/excel ^3.1, PHPUnit bawaan skeleton (tanpa Pest).

**Spec:** `docs/superpowers/specs/2026-09-10-lafagen-design.md`

## Global Constraints

- PHP **≥ 8.2** WAJIB sebelum Task 1 Step 1 — user upgrade PHP Laragon sendiri. STOP jika `php -v` masih 8.1.
- Working dir: `D:/laragon/www/laravel/lafagen`. Direktori sudah berisi `.git` + `docs/` — scaffold harus lewat folder tmp lalu dipindah (jangan `create-project` di dir berisi file).
- Semua copy UI **Bahasa Indonesia**.
- **Jangan** pakai route-model-binding implisit untuk resource terscope komunitas — selalu `Model::where('community', $c->value)->findOrFail($id)`.
- Klausa `where()` terima enum sebagai `->value`; `create([...])` boleh objek enum (cast).
- Query portable **SQLite + MySQL**: tanpa `YEAR()`/`strftime` mentah — pakai `whereYear`/`whereMonth`.
- Test: SQLite in-memory (default `phpunit.xml` Laravel 11 sudah `DB_CONNECTION=sqlite` + `:memory:` — verifikasi, jangan di-repoint ke MySQL).
- Foto: `Storage::fake('public')` di test.
- Format tanggal UI: `d M Y`.
- Shape props Inertia yang dibagikan (kontrak semua task):
  - `auth.user`: `{ id, name, email, role:'admin'|'anggota', community:'fad'|'genre' }` | `null`
  - `community`: `{ key, name, short, title, logo }` | `null`
  - `flash`: `{ success, error }`
- Akhir tiap task: seluruh test PASS + `npm run build` sukses + commit conventional.

---

### Task 1: Scaffold Laravel 11 + Inertia + Vue + Tailwind + shadcn-vue

**Files:**
- Create: seluruh kerangka proyek (dari `lafagen-tmp` via robocopy)
- Modify: `vite.config.js`, `resources/views/app.blade.php`, `bootstrap/app.php`, `.env`
- Create: `resources/js/app.js`, `jsconfig.json`, `tailwind.config.js` (init), `components.json` (shadcn init)
- Create: `resources/js/Pages/Welcome.vue` (sanity; dihapus Task 2)
- Test: `tests/Feature/HealthTest.php` (ganti `ExampleTest.php`)

**Interfaces:**
- Produces: proyek boots; `Inertia::render('X')` me-render `resources/js/Pages/X.vue`; `@/` alias → `resources/js`; komponen shadcn tersedia di `resources/js/components/ui/*`.

- [ ] **Step 0 — Prasyarat PHP:** mintakan user upgrade PHP Laragon ke 8.3 (Laragon > PHP > Version), verifikasi `php -v` ≥ 8.2. Jangan lanjut sebelum lolos.
- [ ] **Step 1 — Scaffold** (dari `cmd`, bukan PowerShell):

```bat
cd /d D:\laragon\www\laravel
composer create-project laravel/laravel:^11.0 lafagen-tmp --prefer-dist --no-interaction
robocopy "D:\laragon\www\laravel\lafagen-tmp" "D:\laragon\www\laravel\lafagen" /E /MOVE /NFL /NDL /NJH /NJS
rmdir /s /q "D:\laragon\www\laravel\lafagen\.git"
cd /d "D:\laragon\www\laravel\lafagen"
git init -b main && git add -A && git commit -m "chore: scaffold Laravel 11 skeleton"
```

Catatan: robocopy exit code 1 = SUKSES (ada file dipindah), bukan error. docs/ lama tetap ada (hanya `.git` tmp yang ditimpa — bila robocopy memindah `.git` tmp ke `lafagen\.git`, rmdir di atas menghapusnya, lalu `git init` ulang; file docs/ tidak tersentuh karena tidak ada di tmp).
- [ ] **Step 2 — Env + DB:** `copy .env.example .env` → set `APP_NAME=Lafagen`, `APP_URL=http://localhost:8000`, `DB_CONNECTION=mysql`, `DB_DATABASE=lafagen`, `DB_USERNAME=root`, `DB_PASSWORD=` (kosong). Lalu:

```bat
mysql -u root -e "CREATE DATABASE IF NOT EXISTS lafagen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan key:generate
```

- [ ] **Step 3 — Inertia + Vue:**

```bat
composer require inertiajs/inertia-laravel:^2.0
npm install @inertiajs/vue3 vue
npm install -D @vitejs/plugin-vue
composer dump-autoload
php artisan vendor:publish --tag=inertia-middleware
```

`vite.config.js` (ganti penuh):

```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [
    laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true }),
    vue({ template: { transformAssetUrls: { base: null, includeAbsolute: false } } }),
  ],
  resolve: {
    alias: { '@': fileURLToPath(new URL('./resources/js', import.meta.url)) },
  },
})
```

`jsconfig.json`:

```json
{
  "compilerOptions": { "baseUrl": ".", "paths": { "@/*": ["resources/js/*"] } },
  "exclude": ["node_modules", "public", "vendor"]
}
```

`bootstrap/app.php` → dalam `->withMiddleware(...)` tambah:

```php
$middleware->web(append: [\App\Http\Middleware\HandleInertiaRequests::class]);
```

- [ ] **Step 4 — Tailwind + shadcn-vue:**

```bat
npm install -D tailwindcss@^3.4 postcss autoprefixer @tailwindcss/forms tailwindcss-animate
npx tailwindcss init -p
npx shadcn-vue@1 init -y -b slate
npx shadcn-vue@1 add button card input label textarea select table badge dialog dropdown-menu sonner avatar separator sheet --yes
```

(`@1` = versi CLI yang masih mendukung Tailwind v3. Jika `init` tetap interaktif, jawab: style `new-york`, css variables `yes`, base color `slate`.) Pastikan `tailwind.config.js` `content`:

```js
content: [
  './resources/views/**/*.blade.php',
  './resources/js/**/*.{vue,js,ts}',
],
```

- [ ] **Step 5 — Wiring render.** `resources/js/app.js` (ganti penuh):

```js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

createInertiaApp({
  title: (t) => (t ? `${t} — Lafagen` : 'Lafagen'),
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) }).use(plugin).mount(el)
  },
  progress: { color: 'var(--primary)' },
})
```

`resources/views/app.blade.php` (ganti penuh):

```blade
<!DOCTYPE html>
<html lang="id" data-community="{{ data_get($page, 'props.community.key', 'public') }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title inertia>{{ data_get($page, 'props.title', 'Lafagen') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="min-h-screen bg-background font-sans antialiased">
        @inertia
    </body>
</html>
```

Catatan: tanpa `ziggy`/`@routes` — semua href Inertia biasa.
Hapus `resources/views/welcome.blade.php`. `routes/web.php` (ganti penuh):

```php
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('landing.sanity');
```

`resources/js/Pages/Welcome.vue`:

```vue
<template>
  <div class="p-10 text-2xl font-bold">Lafagen scaffold OK</div>
</template>
```

- [ ] **Step 6 — Test sanity.** Hapus `tests/Feature/ExampleTest.php`, buat `tests/Feature/HealthTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_serves_landing(): void
    {
        $this->get('/')->assertOk()->assertSee('Lafagen scaffold OK');
    }

    public function test_health_endpoint(): void
    {
        $this->get('/up')->assertOk();
    }
}
```

- [ ] **Step 7 — Verifikasi:**

```bat
php artisan migrate --force
php artisan test
npm run build
```

Expected: 2 test PASS, build sukses.
- [ ] **Step 8 — Commit:** `git add -A && git commit -m "chore: Inertia + Vue + Tailwind + shadcn-vue wiring"`

---

### Task 2: Komunitas: enum, config, tema, landing, login

**Files:**
- Create: `app/Enums/Community.php`, `app/Enums/UserRole.php`, `config/communities.php`
- Create: `app/Http/Middleware/EnsureCommunity.php`, `app/Http/Middleware/EnsureAdmin.php`
- Create: `app/Http/Controllers/Auth/LoginController.php`, `app/Http/Requests/Auth/LoginRequest.php`
- Create: `resources/js/Pages/Landing.vue`, `resources/js/Pages/Auth/Login.vue`, `resources/js/composables/useCommunity.js`
- Create: `public/images/fad.png`, `public/images/genre.png` (placeholder 1×1; user ganti aset brand)
- Modify: `bootstrap/app.php`, `routes/web.php`, `resources/css/app.css`, `app/Http/Middleware/HandleInertiaRequests.php`, hapus `Pages/Welcome.vue`
- Test: `tests/Feature/AuthTest.php`, `tests/Unit/CommunityEnumTest.php`

**Interfaces:**
- Consumes: Task 1.
- Produces: `Community::{FAD,GENRE}` (backed string), `UserRole::{ADMIN,ANGGOTA}`; `config('communities.{key}')` → `['key','name','short','title','logo']`; middleware alias `community`, `admin`; guest redirect → `/{community}/login`; shared props sesuai Global Constraints (property `auth.user` baru terisi setelah Task 3).

- [ ] **Step 1 — Tulis test gagal.** `tests/Unit/CommunityEnumTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Enums\Community;
use PHPUnit\Framework\TestCase;

class CommunityEnumTest extends TestCase
{
    public function test_values(): void
    {
        $this->assertSame(['fad', 'genre'], array_column(Community::cases(), 'value'));
        $this->assertNull(Community::tryFrom('salah'));
        $this->assertSame('FAD Tanah Laut', Community::FAD->config()['title']);
        $this->assertSame('GENRE Tanah Laut', Community::GENRE->config()['title']);
    }
}
```

`tests/Feature/AuthTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_renders(): void
    {
        $this->get('/')->assertOk()
            ->assertInertia(fn ($p) => $p->component('Landing')->where('community', null));
    }

    public function test_login_page_per_community(): void
    {
        $this->get('/fad/login')->assertOk()->assertInertia(
            fn ($p) => $p->component('Auth/Login')->where('community.key', 'fad')
                ->where('community.title', 'FAD Tanah Laut')
        );
        $this->get('/genre/login')->assertOk()
            ->assertInertia(fn ($p) => $p->where('community.key', 'genre'));
    }

    public function test_unknown_community_404(): void
    {
        $this->get('/xxx/login')->assertNotFound();
    }

    public function test_guest_redirected_to_matching_login(): void
    {
        $this->get('/genre/dashboard')->assertRedirect('/genre/login');
    }
}
```

- [ ] **Step 2 — Run** `php artisan test --filter="AuthTest|CommunityEnumTest"` → FAIL.
- [ ] **Step 3 — Backend.**

`app/Enums/Community.php`:

```php
<?php

namespace App\Enums;

enum Community: string
{
    case FAD = 'fad';
    case GENRE = 'genre';

    public function config(): array
    {
        return config("communities.{$this->value}");
    }
}
```

`app/Enums/UserRole.php`:

```php
<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case ANGGOTA = 'anggota';

    public function label(): string
    {
        return $this === self::ADMIN ? 'Admin' : 'Anggota';
    }
}
```

`config/communities.php`:

```php
<?php

return [
    'fad' => [
        'key' => 'fad',
        'name' => 'Forum Anak Daerah',
        'short' => 'FAD',
        'title' => 'FAD Tanah Laut',
        'logo' => '/images/fad.png',
    ],
    'genre' => [
        'key' => 'genre',
        'name' => 'Generasi Berencana',
        'short' => 'GENRE',
        'title' => 'GENRE Tanah Laut',
        'logo' => '/images/genre.png',
    ],
];
```

`app/Http/Middleware/EnsureCommunity.php`:

```php
<?php

namespace App\Http\Middleware;

use App\Enums\Community;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommunity
{
    public function handle(Request $request, Closure $next): Response
    {
        $community = Community::tryFrom((string) $request->route('community'));
        abort_if($community === null, 404);

        if ($user = $request->user()) {
            if ($user->community !== $community) {
                return redirect('/' . $user->community->value . '/dashboard');
            }
            if ($request->routeIs('community.login')) {
                return redirect('/' . $community->value . '/dashboard');
            }
        }

        $request->attributes->set('community', $community);

        return $next($request);
    }
}
```

`app/Http/Middleware/EnsureAdmin.php`:

```php
<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === UserRole::ADMIN, 403);

        return $next($request);
    }
}
```

`bootstrap/app.php` — `withMiddleware` final:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);
    $middleware->alias([
        'community' => \App\Http\Middleware\EnsureCommunity::class,
        'admin' => \App\Http\Middleware\EnsureAdmin::class,
    ]);
    $middleware->redirectGuestsTo(
        fn (\Illuminate\Http\Request $request) =>
            '/' . ($request->route('community') ?? 'fad') . '/login'
    );
})
```

`routes/web.php` (ganti penuh; hapus Route Task 1):

```php
<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Landing')->name('landing');

Route::prefix('{community}')
    ->where('community', 'fad|genre')
    ->middleware('community')
    ->name('community.')
    ->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])
            ->middleware('throttle:10,1')->name('login.store');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::middleware('auth')->group(function () {
            Route::get('/', fn (\Illuminate\Http\Request $r) => redirect('/' . $r->route('community') . '/dashboard'));
            Route::view('dashboard', 'placeholder')->name('dashboard'); // diganti Task 6
        });
    });
```

`app/Http/Requests/Auth/LoginRequest.php` — standar Breeze-style dengan pesan Indonesia (rate limit 5/menit, `throttleKey` = email+IP):

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'email' => 'Terlalu banyak percobaan login. Coba lagi dalam '
                . RateLimiter::availableIn($this->throttleKey()) . ' detik.',
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
```

`app/Http/Controllers/Auth/LoginController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Login', [
            'title' => 'Masuk',
            'community' => $request->attributes->get('community')->config(),
        ]);
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(
            '/' . $request->user()->community->value . '/dashboard'
        );
    }

    public function destroy(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
```

`HandleInertiaRequests::share()` (ganti isinya; `$this->auth` default parent tetap):

```php
public function share(Request $request): array
{
    $user = $request->user();
    $communityKey = $request->route('community') ?? $user?->community?->value;

    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'community' => $user->community->value,
            ] : null,
        ],
        'community' => $communityKey ? config("communities.{$communityKey}") : null,
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ]);
}
```

`resources/views/placeholder.blade.php` (sementara, dihapus Task 6):

```blade
<div style="padding:2rem">Dashboard — implementasi Task 6</div>
```

- [ ] **Step 4 — Tema CSS.** Tambah di `resources/css/app.css` SETELAH blok `:root`/`.dark` hasil shadcn init (nilai HSL tanpa prefix `hsl()` karena template shadcn v3 memakai `hsl(var(--x))` — cek file hasil init; jika pakai oklch, tulis nilai oklch):

```css
@layer base {
  [data-community='fad'] {
    --primary: 166 75% 28%;
    --primary-foreground: 0 0% 100%;
    --ring: 166 75% 28%;
    --accent: 162 73% 92%;
    --sidebar-accent: 162 73% 92%;
  }
  [data-community='genre'] {
    --primary: 205 90% 40%;
    --primary-foreground: 0 0% 100%;
    --ring: 205 90% 40%;
    --accent: 204 94% 93%;
    --sidebar-accent: 204 94% 93%;
  }
}
```

Jika `--sidebar-accent` tidak dikenali tema shadcn-mu, hapus baris itu (variabel lain cukup — sidebar memakai `--accent`). Komponen:

```css
@layer components {
  .nav-link {
    @apply flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-foreground/70 hover:bg-accent hover:text-foreground;
  }
  .nav-link-active {
    @apply bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground;
  }
}
```

- [ ] **Step 5 — Frontend.**

`resources/js/composables/useCommunity.js`:

```js
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useCommunity() {
  const community = computed(() => usePage().props.community)
  const url = (path = '') =>
    `/${community.value.key}${path ? '/' + path.replace(/^\/+/, '') : ''}`
  return { community, url }
}
```

`resources/js/Pages/Landing.vue`: heading "Lafagen — Sistem Laporan Program Kerja" + subteks "Kabupaten Tanah Laut"; grid 2 kartu `<Link href="/fad/login">` / `/genre/login` (label statis "FAD — Forum Anak Daerah" dan "GENRE — Generasi Berencana"; props `community` sengaja `null` di landing, jadi tidak dibaca).
`resources/js/Pages/Auth/Login.vue`: Card di tengah; `<img :src="community.logo">` (event `@error` menyembunyikan img), judul `community.title`; form via `useForm({ email:'', password:'', remember:false })` dengan aksi POST eksplisit:

```js
const action = `/${usePage().props.community.key}/login`
form.post(action, { preserveScroll: true, onFinish: () => form.reset('password') })
```

Error per-field ditampilkan `text-destructive`; link "← Kembali pilih komunitas" ke `/`. Pakai `Button`, `Card`, `Input`, `Label` dari `@/components/ui/*`.

- [ ] **Step 6 — Verifikasi:** `php artisan test --filter="AuthTest|CommunityEnumTest|HealthTest"` → semua PASS (HealthTest landing kini komponen `Landing` — update assertion menjadi `assertSee('Lafagen')` yang masih cocok dengan heading). Hapus `Pages/Welcome.vue`. `npm run build` sukses. Placeholder PNG dibuat via `php -r "file_put_contents('public/images/fad.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mN08PevAIEBrQPF0Iz+AAAAAElFTkSuQmCC'));"` (salin untuk genre.png).
- [ ] **Step 7 — Smoke manual:** `php artisan serve` — buka `/` klik kedua kartu, halaman login tampil beda warna (cek devtools `html[data-community]`).
- [ ] **Step 8 — Commit:** `git add -A && git commit -m "feat: fondasi komunitas, tema, landing, dan login"`

---

### Task 3: Skema data, model, factory, seeder

**Files:**
- Modify: `database/migrations/0001_01_01_000000_create_users_table.php`, `app/Models/User.php`, `database/factories/UserFactory.php`, `database/seeders/DatabaseSeeder.php`
- Create: migration `create_categories_table`, `create_reports_table`, `create_report_photos_table`; model `Category`, `Report`, `ReportPhoto`; factory `CategoryFactory`, `ReportFactory`; seeder `AdminUserSeeder`, `CategorySeeder`, `DemoReportSeeder`
- Create: `.env`/`.env.example` tambah `SEED_ADMIN_PASSWORD=password`
- Test: `tests/Feature/SchemaModelTest.php`, `tests/Feature/CommunityIsolationTest.php`

**Interfaces:**
- Produces:
  - `User` casts: `community`→Community, `role`→UserRole, `password`→hashed; `reports(): HasMany`; helper `isAdmin(): bool`.
  - `Report` fillable `title,description,start_date,end_date,location,category_id`; casts `start_date`/`end_date`→`date`; relasi `user()`, `category()`, `photos()`; `Report::PER_PAGE = 15`; **`Report::filtered(Community $c, array $f = []): Builder`** — filter keys: `month` (int), `year` (int), `category_id`, `q` — selalu tambah `where('community', $c->value)`; ordering default `start_date desc, id desc`.
  - `Category::for(Community $c): Builder`, `Category::optionsFor(Community $c)` → `[['id','name'],...]`.
  - `ReportPhoto` relasi `report()`; kolom `path`, `original_name`.
  - Seed: 2 admin (`admin@lafagen.test`, `admin-genre@lafagen.test`, password `env('SEED_ADMIN_PASSWORD','password')`), 6 kategori default × 2 komunitas, ±24 laporan demo FAD + 8 GENRE (spread Jun 2025 – Sep 2026). Laporan demo TANPA baris `report_photos` (galeri kosong = state valid; upload diuji lewat test dengan `Storage::fake`).

- [ ] **Step 1 — Tulis test gagal.** `tests/Feature/SchemaModelTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_casts(): void
    {
        $user = User::factory()->create([
            'community' => Community::FAD,
            'role' => UserRole::ADMIN,
        ]);
        $fresh = $user->fresh();
        $this->assertSame(Community::FAD, $fresh->community);
        $this->assertSame(UserRole::ADMIN, $fresh->role);
        $this->assertTrue($fresh->isAdmin());
    }

    public function test_filtered_scopes_by_community_and_filters(): void
    {
        $fadCat = Category::factory()->create(['community' => Community::FAD]);
        Report::factory()->create([
            'community' => Community::FAD,
            'category_id' => $fadCat->id,
            'start_date' => '2026-03-10',
        ]);
        Report::factory()->create([
            'community' => Community::FAD,
            'category_id' => $fadCat->id,
            'start_date' => '2026-04-20',
        ]);
        Report::factory()->create([
            'community' => Community::GENRE,
            'category_id' => Category::factory()->create(['community' => Community::GENRE])->id,
            'start_date' => '2026-03-15',
        ]);

        $this->assertSame(2, Report::filtered(Community::FAD, [])->count());
        $this->assertSame(1, Report::filtered(Community::FAD, ['year' => 2026, 'month' => 3])->count());
        $this->assertSame(0, Report::filtered(Community::FAD, ['year' => 2026, 'month' => 5])->count());
    }

    public function test_category_unique_per_community(): void
    {
        Category::factory()->create(['community' => Community::FAD, 'name' => 'Pendidikan']);
        $this->expectException(QueryException::class);
        Category::factory()->create(['community' => Community::FAD, 'name' => 'Pendidikan']);
    }

    public function test_seeders_run(): void
    {
        $this->seed();
        $this->assertSame(2, User::where('role', UserRole::ADMIN->value)->count());
        $this->assertSame(12, Category::count());
        $this->assertGreaterThan(20, Report::count());
    }
}
```

- [ ] **Step 2 — Run** `php artisan test --filter=SchemaModelTest` → FAIL.
- [ ] **Step 3 — Migrasi users.** Edit `create_users_table` existing (sebelum jalankan apa pun — DB dev masih fresh; jika sudah pernah dimigrasi: `php artisan migrate:fresh`):

```php
$table->enum('community', ['fad', 'genre'])->default('fad')->after('password');
$table->enum('role', ['admin', 'anggota'])->default('anggota')->after('community');
```

- [ ] **Step 4 — Migrasi baru.** `php artisan make:migration create_categories_table` dst (3 migration):

`create_categories_table`:

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->enum('community', ['fad', 'genre']);
    $table->string('name', 100);
    $table->timestamps();
    $table->unique(['community', 'name']);
});
```

`create_reports_table`:

```php
Schema::create('reports', function (Blueprint $table) {
    $table->id();
    $table->enum('community', ['fad', 'genre']);
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->restrictOnDelete();
    $table->string('title', 150);
    $table->text('description');
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->string('location', 150)->nullable();
    $table->timestamps();
    $table->index(['community', 'start_date']);
    $table->index(['community', 'category_id']);
});
```

`create_report_photos_table`:

```php
Schema::create('report_photos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('report_id')->constrained()->cascadeOnDelete();
    $table->string('path');
    $table->string('original_name')->nullable();
    $table->timestamps();
});
```

- [ ] **Step 5 — Model.** `app/Models/User.php` — tambah di atas properti existing:

```php
use App\Enums\Community;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\HasMany;

// $fillable += 'community', 'role'
// casts() += 'community' => Community::class, 'role' => UserRole::class (password tetap hashed)

public function reports(): HasMany
{
    return $this->hasMany(Report::class);
}

public function isAdmin(): bool
{
    return $this->role === UserRole::ADMIN;
}
```

`app/Models/Category.php`:

```php
<?php

namespace App\Models;

use App\Enums\Community;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected function casts(): array
    {
        return ['community' => Community::class];
    }

    public function scopeFor(Builder $q, Community $c): Builder
    {
        return $q->where('community', $c->value);
    }

    public static function optionsFor(Community $c): array
    {
        return static::for($c)->orderBy('name')->get(['id', 'name'])
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])->all();
    }
}
```

Catatan: `community` diisi eksplisit saat create (factory/`Category::create(['community' => ..., 'name' => ...])`) — TIDAK di-`fillable` mass-assignable dari request; set manual di controller/factory (`$model->community = Community::FAD` atau passing ke create dengan guard).

`app/Models/Report.php`:

```php
<?php

namespace App\Models;

use App\Enums\Community;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    public const PER_PAGE = 15;

    protected $fillable = [
        'title', 'description', 'start_date', 'end_date', 'location', 'category_id',
    ];

    protected function casts(): array
    {
        return [
            'community' => Community::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }

    public static function filtered(Community $community, array $f = []): Builder
    {
        $q = static::query()->where('community', $community->value);

        if (! empty($f['year'])) {
            $q->whereYear('start_date', (int) $f['year']);
        }
        if (! empty($f['month'])) {
            $q->whereMonth('start_date', (int) $f['month']);
        }
        if (! empty($f['category_id'])) {
            $q->where('category_id', (int) $f['category_id']);
        }
        if (! empty($f['q'])) {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $f['q']) . '%';
            $q->where(function (Builder $sub) use ($like) {
                $sub->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('location', 'like', $like);
            });
        }

        return $q->latest('start_date')->latest('id');
    }
}
```

`app/Models/ReportPhoto.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'original_name'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
```

- [ ] **Step 6 — Factories.** `UserFactory` tambah default `'community' => Community::FAD, 'role' => UserRole::ANGGOTA`. `CategoryFactory`:

```php
public function definition(): array
{
    return ['community' => Community::FAD, 'name' => fake()->unique()->randomElement([
        'Pendidikan', 'Sosial', 'Lingkungan', 'Kewirausahaan', 'Seni Budaya',
        'Olahraga', 'Keagamaan', 'Keterampilan',
    ])];
}
```

`ReportFactory`:

```php
public function definition(): array
{
    $start = fake()->dateTimeBetween('-15 months', 'now');

    return [
        'community' => Community::FAD,
        'user_id' => User::factory(),
        'category_id' => Category::factory(),
        'title' => fake()->sentence(4),
        'description' => fake()->paragraph(),
        'start_date' => $start->format('Y-m-d'),
        'end_date' => fake()->boolean(30)
            ? (clone $start)->modify('+2 days')->format('Y-m-d')
            : null,
        'location' => fake()->city(),
    ];
}
```

Catatan implementasi: pada test, `Report::factory()->create(['category_id' => X])` harus mencegah pembuatan kategori ganda — `ReportFactory` memakai `['category_id' => Category::factory()]` hanya bila tidak di-override (perilaku default factory: state yang diberikan menggantikan, OK tanpa kode khusus). Pastikan `user_id`/`community` laporan konsisten di test isolation (Task 7) — factory TIDAK menyetel `community` dari user; selalu pass eksplisit di test.

- [ ] **Step 7 — Seeders.** `CategorySeeder` (6 nama default × 2 komunitas: Pendidikan, Sosial & Kemasyarakatan, Lingkungan Hidup, Keagamaan, Keterampilan, Lainnya — pakai `firstOrCreate`). `AdminUserSeeder`: dua user admin sesuai Interfaces; password `config`/`env('SEED_ADMIN_PASSWORD','password')` via `Hash::make`. `DemoReportSeeder`: loop komunitas → ambil kategori milik komunitas tsb; buat laporan via `Report::factory()->for($admin)` dengan `community` + `category_id` eksplisit; judul realistis (contoh: "Pelatihan Leadership Remaja", "Aksi Bersih Pantai Ambawang"…) spread bulan; idempoten (lewati bila sudah ada laporan). `DatabaseSeeder::run()` memanggil ketiganya. Tambah `SEED_ADMIN_PASSWORD=password` di `.env` dan `.env.example`.
- [ ] **Step 8 — Jalankan:**

```bat
php artisan migrate:fresh --seed
php artisan test --filter=SchemaModelTest
```

Expected: PASS (4 test).
- [ ] **Step 9 — Test isolasi komunitas** `tests/Feature/CommunityIsolationTest.php` — validasi lintas prefix DITOLAK level HTTP (route auth baru ada Task 6; test ini untuk Task 6+; tulis sekarang tapi `markTestIncomplete` TIDAK diperbolehkan — jadi: tulis sekarang HANYA unit-level):

```php
<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtered_never_leaks_across_communities(): void
    {
        Report::factory()->create(['community' => Community::FAD]);
        $genre = Report::factory()->create(['community' => Community::GENRE]);

        $fadIds = Report::filtered(Community::FAD, [])->pluck('id')->all();
        $this->assertNotContains($genre->id, $fadIds);
    }
}
```

Run → PASS. Commit: `git add -A && git commit -m "feat: skema data komunitas, model, factory, seeder"`

---

### Task 4: CRUD Laporan (backend) + Policy + upload foto

**Files:**
- Create: `app/Policies/ReportPolicy.php`, `app/Http/Requests/StoreReportRequest.php`, `app/Http/Controllers/ReportController.php`, `app/Http/Requests/UpdateReportRequest.php`
- Modify: `app/Providers/AppServiceProvider.php` (gate policy otomatis via naming — Laravel 11 auto-discover `Report`→`ReportPolicy`, cukup pastikan tidak ada pendaftaran salah), `routes/web.php` (grup laporan), `config/filesystems.php` (tidak diubah; pakai disk `public`)
- Test: `tests/Feature/ReportTest.php`

**Interfaces:**
- Consumes: Task 2 (middleware `community`, request attribute `community`), Task 3 (`Report::filtered`, enum, `PER_PAGE`).
- Produces: route names `community.reports.*` — `index` GET `/reports`, `create` GET `/reports/create`, `store` POST, `show` GET `/reports/{report}`, `edit` GET `/reports/{report}/edit`, `update` PUT, `destroy` DELETE. Controller membaca komunitas dari `$request->attributes->get('community')` (di-set EnsureCommunity). Helper internal `syncPhotos(Report $r, ?array $files)` untuk store & update. Props halaman: `Reports/Index.vue {filters, reports(lengthAwarePaginator)}`, `Reports/Form.vue {report|null, categories}` (dipakai Task 5), `Reports/Show.vue {report: {...photos:[{id,url,original_name}], editable:bool}}`.

- [ ] **Step 1 — Test gagal** `tests/Feature/ReportTest.php` (fakta kunci; implementer menulis assertion lengkap sesuai komponen ini):

```php
<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function user($community = Community::FAD, $role = 'anggota'): User
    {
        return User::factory()->create(['community' => $community, 'role' => $role]);
    }

    private function payload(User $u): array
    {
        return [
            'title' => 'Kegiatan Literasi',
            'category_id' => Category::for($u->community)->value('id'),
            'start_date' => '2026-05-01',
            'end_date' => null,
            'location' => 'Banjarmasin',
            'description' => 'Deskripsi kegiatan.',
        ];
    }

    public function test_guest_redirected(): void
    {
        $this->get('/fad/reports')->assertRedirect('/fad/login');
    }

    public function test_anggota_can_create_with_photos(): void
    {
        Storage::fake('public');
        $u = $this->user();
        Category::factory()->create(['community' => Community::FAD]);

        $res = $this->actingAs($u)->post('/fad/reports', $this->payload($u) + [
            'photos' => [UploadedFile::fake()->image('a.jpg', 800, 600)],
        ]);

        $report = Report::sole();
        $this->assertSame(Community::FAD, $report->community);
        $this->assertSame($u->id, $report->user_id);
        $res->assertRedirect(route('community.reports.show', ['community' => 'fad', 'report' => $report]));
        Storage::disk('public')->assertExists("reports/fad/{$report->id}/" . basename($report->photos()->value('path')));
    }

    public function test_anggota_cannot_edit_others_report(): void
    {
        $owner = $this->user();
        $other = $this->user();
        Category::factory()->create(['community' => Community::FAD]);
        $r = Report::factory()->for($owner)->create(['community' => Community::FAD, 'category_id' => Category::for(Community::FAD)->value('id')]);

        $this->actingAs($other)->get("/fad/reports/{$r->id}/edit")->assertForbidden();
        $this->actingAs($other)->put("/fad/reports/{$r->id}", ['title' => 'x'])->assertForbidden();
        $this->actingAs($other)->delete("/fad/reports/{$r->id}")->assertForbidden();
        $this->actingAs($owner)->get("/fad/reports/{$r->id}/edit")->assertOk();
    }

    public function test_admin_can_edit_others_report(): void
    {
        $admin = $this->user(role: 'admin');
        $r = Report::factory()->for($this->user())->create([
            'community' => Community::FAD,
            'category_id' => Category::factory()->create(['community' => Community::FAD])->id,
        ]);
        $this->actingAs($admin)->put("/fad/reports/{$r->id}", ['title' => 'Diedit admin'])->assertRedirect();
        $this->assertSame('Diedit admin', $r->fresh()->title);
    }

    public function test_genre_user_cannot_touch_fad_report(): void
    {
        $r = Report::factory()->create([
            'community' => Community::FAD,
            'category_id' => Category::factory()->create(['community' => Community::FAD])->id,
        ]);
        $g = $this->user(Community::GENRE);
        $this->actingAs($g)->get("/genre/reports/{$r->id}")->assertNotFound();
        $this->actingAs($g)->delete("/genre/reports/{$r->id}")->assertNotFound();
    }

    public function test_category_must_belong_to_same_community(): void
    {
        $u = $this->user();
        $genreCat = Category::factory()->create(['community' => Community::GENRE]);

        $this->actingAs($u)->post('/fad/reports', [
            'title' => 'X', 'category_id' => $genreCat->id,
            'start_date' => '2026-05-01', 'description' => 'Y',
        ])->assertSessionHasErrors('category_id');
        $this->assertSame(0, Report::count());
    }

    public function test_validation_rules(): void
    {
        $u = $this->user();
        Category::factory()->create(['community' => Community::FAD]);
        $catId = Category::for(Community::FAD)->value('id');

        $this->actingAs($u)->post('/fad/reports', [
            'title' => '', 'category_id' => $catId, 'start_date' => '2026-05-10',
            'end_date' => '2026-05-05', 'description' => '',
        ])->assertSessionHasErrors(['title', 'description', 'end_date']);
    }

    public function test_index_filters_paginates(): void
    {
        $u = $this->user();
        $cat = Category::factory()->create(['community' => Community::FAD]);
        Report::factory()->count(20)->for($u)->create([
            'community' => Community::FAD, 'category_id' => $cat->id, 'start_date' => '2026-02-01',
        ]);

        $this->actingAs($u)->get('/fad/reports')->assertOk()
            ->assertInertia(fn ($p) => $p->component('Reports/Index')
                ->where('reports.total', 20)
                ->has('reports.data', Report::PER_PAGE));
        $this->actingAs($u)->get('/fad/reports?month=3&year=2026')
            ->assertInertia(fn ($p) => $p->where('reports.total', 0));
    }

    public function test_destroy_removes_files(): void
    {
        Storage::fake('public');
        $u = $this->user();
        $cat = Category::factory()->create(['community' => Community::FAD]);
        $r = Report::factory()->for($u)->create(['community' => Community::FAD, 'category_id' => $cat->id]);
        $r->photos()->create(['path' => 'reports/fad/' . $r->id . '/x.jpg', 'original_name' => 'x.jpg']);

        $this->actingAs($u)->delete("/fad/reports/{$r->id}")->assertRedirect('/fad/reports');
        $this->assertSame(0, Report::count());
        Storage::disk('public')->assertMissing('reports/fad/' . $r->id . '/x.jpg');
    }
}
```

- [ ] **Step 2 — Run** `php artisan test --filter=ReportTest` → FAIL.
- [ ] **Step 3 — Request.** `StoreReportRequest`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category_id' => [
                'required', 'integer',
                Rule::exists('categories', 'id')->where('community', $this->route('community')),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:20000'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Kategori tidak valid untuk komunitas Anda.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
            'photos.max' => 'Maksimal 10 foto.',
            'photos.*.image' => 'File foto harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 5 MB.',
        ];
    }
}
```

`UpdateReportRequest extends StoreReportRequest` dengan penyesuaian: `photos` tetap nullable; tambah `remove_photo_ids` => `['nullable','array']` + `remove_photo_ids.*` integer (divalidasi milik laporan saat destroy foto — lihat Step 4).

- [ ] **Step 4 — Policy.** `app/Policies/ReportPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function update(User $user, Report $report): bool
    {
        return $report->user_id === $user->id;
    }

    public function delete(User $user, Report $report): bool
    {
        return $report->user_id === $user->id;
    }
}
```

(Laravel 11 auto-discover policy ini; `create`/`view` default true — view di-scope komunitas via query, bukan policy.)

- [ ] **Step 5 — Controller** `app/Http/Controllers/ReportController.php`. Poin penting implementasi:

```php
private function community(\Illuminate\Http\Request $request): \App\Enums\Community
{
    return $request->attributes->get('community');
}

// index: $filters = $request->only(['month','year','category_id','q']);
//   $reports = Report::filtered($c, $filters)->with(['category:id,name','user:id,name'])
//       ->paginate(Report::PER_PAGE)->withQueryString();
//   render('Reports/Index', compact('reports','filters') + ['categories' => Category::optionsFor($c), 'title' => 'Laporan'])
// show: $r = Report::filtered($c)->with(['user:id,name','category:id,name','photos'])->findOrFail($id);
//   photos url: asset('storage/'.$p->path); 'editable' => $request->user()->can('update', $r)
// store: $r = new Report($data); $r->community = $c; $r->user_id = $request->user()->id;
//   $r->save(); syncPhotos(); redirect route show + flash success 'Laporan tersimpan.'
// update: authorize('update',$r); $r->update($data); hapus foto di remove_photo_ids
//   (whereIn id AND report_id → unlink file + delete row), syncPhotos(); redirect show.
// destroy: authorize('delete',$r); hapus file photos->pluck('path') lalu $r->delete();
//   redirect('/{c}/reports') flash 'Laporan dihapus.'
```

`syncPhotos(Report $report, ?array $files)`: loop `$files ?? []` → `$name = Str::uuid().'.'.$file->extension(); $file->storeAs("reports/{$report->community->value}/{$report->id}", $name, 'public');` → `ReportPhoto::create(['report_id','path'=>"reports/{$report->community->value}/{$report->id}/{$name}", 'original_name'=>$file->getClientOriginalName()])`.

- [ ] **Step 6 — Routes.** Dalam grup `community` + `auth` di `routes/web.php` (ganti `Route::view('dashboard'...)` tetap sampai Task 6):

```php
Route::resource('reports', \App\Http\Controllers\ReportController::class);
```

Implementasi final: `Route::resource('reports', ReportController::class);` penuh (7 aksi; `create`/`edit` render form).
- [ ] **Step 7 — Run** `php artisan test --filter=ReportTest` → PASS.
- [ ] **Step 8 — Commit:** `git commit -am "feat: CRUD laporan dengan policy komunitas dan upload foto"` (git add -A).

---

### Task 5: UI Laporan (tabel, form, detail, galeri)

**Files:**
- Create: `resources/js/Layouts/CommunityLayout.vue`, `resources/js/Components/AppShell.vue` (opsional), `resources/js/Pages/Reports/Index.vue`, `resources/js/Pages/Reports/Form.vue`, `resources/js/Pages/Reports/Show.vue`, `resources/js/Components/PhotoUploader.vue`, `resources/js/Components/EmptyState.vue`
- Test: `npm run build` (no PHPUnit untuk Vue)

**Interfaces:**
- Consumes: props Task 4 (`Reports/Index {reports,categories,filters}`, `Form`, `Show`), Task 2 (`useCommunity`, `.nav-link`, flash share, tema per `data-community`), komponen shadcn `button card input label textarea select table badge dialog`.
- Produces: `CommunityLayout.vue` dipakai Task 6, 8, 9 — **kontrak**: `<CommunityLayout title="...">` slot default; sidebar (logo+nama komunitas dari `useCommunity`, menu Dashboard/Laporan + Kategori/Pengguna bila `auth.user.role==='admin'`), topbar dengan nama user + dropdown logout (POST `url('logout')`). Flash `success/error` ditampikan via `Sonner` toast (`usePage().props.flash` → `watch` → `toast`).

- [ ] **Step 1 — Layout.** `CommunityLayout.vue` dengan `<aside>` (logo komunitas, menu `<Link class="nav-link" :class="{ 'nav-link-active': current('dashboard') }">` — helper `current(name)` cocokkan `usePage().url.startsWith('/'+key+'/'+name)`), `<main class="p-6 max-w-6xl">` slot. Tombol logout = `useForm().post(url('logout'))`.
- [ ] **Step 2 — Index laporan.** Tabel (Judul, Kategori badge, Tanggal `d M Y` – bila end_date beda tampilkan rentang, Lokasi, Pelapor, jumlah foto icon). Di atas tabel: bar filter — select Bulan (12 opsi + "Semua"), select Tahun (data-driven: tahun ini..-2), select Kategori (`categories` prop), Input pencarian + tombol "Terapkan" memakai `router.get(url('reports'), form, { preserveScroll: true, replace: true })` (query string = sumber kebenaran; tanpa state lokal). Tombol "Tambah Laporan" → `url('reports/create')`. Paginasi: link `reports.links` via `<Link>` (atau helper `router.get(href,{preserveScroll:true})`). Kosong → `<EmptyState>` (teks Indonesia + CTA).
- [ ] **Step 3 — Form create/edit (satu komponen `Form.vue`).** Props `{ report|null, categories }`. `useForm({title,category_id,start_date,end_date,location,description,photos:[],remove_photo_ids:[]})`; input tanggal `type="date"`; deskripsi `Textarea`; kategori `Select` dari `categories`. `PhotoUploader.vue`: input file multiple accept image + preview grid thumbnail; edit mode: foto lama tampil dengan tombol ✕ (push id ke `remove_photo_ids`, sembunyikan preview), foto baru di-append array `photos` (File objects → form data Inertia otomatis multipart). Submit: `form.post(report ? undefined : url('reports'), ...)` — eksplisit: `report ? form.transform(d=>({...d,_method:'put'})).post(url('reports/'+report.id)) : form.post(url('reports'))` dengan `onSuccess` reload otomatis Inertia; error per-field tampil di bawah input.
- [ ] **Step 4 — Show.** Data + galeri grid foto (`aspect-video object-cover`, klik → `<Dialog>` lightbox), tombol Edit/Hapus (Dialog confirm → `form.delete(url('reports/'+report.id), {preserveScroll:true})`) dirender hanya bila `report.editable`.
- [ ] **Step 5 — Build check:** `npm run build` → sukses tanpa error.
- [ ] **Step 6 — Smoke manual:** `php artisan migrate:fresh --seed && php artisan serve` (login `admin@lafagen.test`/`password`) — buat laporan, upload 2 foto, cek galeri, edit, hapus; ulangi singkat di GENRE untuk memastikan data tak campur.
- [ ] **Step 7 — Commit:** `git add -A && git commit -m "feat: UI laporan (tabel filter, form upload, galeri) + layout komunitas"`

---

### Task 6: Dashboard statistik + grafik; hapus placeholder

**Files:**
- Create: `app/Http/Controllers/DashboardController.php`, `resources/js/Pages/Dashboard.vue`, `resources/js/Components/StatCard.vue`
- Modify: `routes/web.php` (route dashboard), hapus `resources/views/placeholder.blade.php`
- Test: `tests/Feature/DashboardTest.php`; `npm install recharts`

**Interfaces:**
- Consumes: layout Task 5; `Report::filtered`; tema.
- Produces: props `Dashboard {stats:{total_reports,total_members,this_month,this_year}, monthly:12×{label:string,total:int}, byCategory:[{name,total,color?}], recent:[{id,title,category_name,start_date,user_name}], year:int}`. Route name `community.dashboard`.

- [ ] **Step 1 — Test gagal** `tests/Feature/DashboardTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Enums\Community;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_props(): void
    {
        $u = User::factory()->create(['community' => Community::FAD]);
        $cat = Category::factory()->create(['community' => Community::FAD]);
        $now = now();
        Report::factory()->count(3)->for($u)->create([
            'community' => Community::FAD, 'category_id' => $cat->id,
            'start_date' => $now->copy(),
        ]);

        $this->actingAs($u)->get('/fad/dashboard')->assertOk()->assertInertia(
            fn ($p) => $p->component('Dashboard')
                ->where('stats.total_reports', 3)
                ->where('stats.total_members', 1)
                ->where('stats.this_month', 3)
                ->where('stats.this_year', 3)
                ->has('monthly', 12)
                ->where('monthly.11.total', 3)   // bulan berjalan = indeks ke-sekian
                ->where('recent.0.title', Report::sole()->title)
        );
    }

    public function test_dashboard_scoped_to_community(): void
    {
        $g = User::factory()->create(['community' => Community::GENRE]);
        Report::factory()->create([
            'community' => Community::FAD,
            'category_id' => Category::factory()->create(['community' => Community::FAD])->id,
        ]);
        $this->actingAs($g)->get('/genre/dashboard')
            ->assertInertia(fn ($p) => $p->where('stats.total_reports', 0));
    }

    public function test_fad_user_cannot_open_genre_dashboard(): void
    {
        $f = User::factory()->create(['community' => Community::FAD]);
        $this->actingAs($f)->get('/genre/dashboard')->assertRedirect('/fad/dashboard');
    }
}
```

Catatan implementer: assertion `monthly.11.total` mengasumsikan bulan berjalan; bila `now()` bukan Desember, sesuaikan indeks saat menulis test — bangun `monthly` selalu 12 entri Januari→Desember tahun `$year`, jadi indeks = `month-1`. Test di atas memakai bulan berjalan sekarang — tulis indeksnya dari `now()->month - 1` secara eksplisit dengan variabel PHP di test (boleh; tetap deterministik).
- [ ] **Step 2 — Controller.** `DashboardController::index`: `$year = (int) request('year', now()->year)` (clamp 2020..now); stats via `Report::filtered($c,...)`; `monthly` = loop 1..12 `whereYear->whereMonth->count()` (12 query — cukup untuk skala komunitas; alternatif 1 `selectRaw` tidak portable, hindari); `byCategory` join kategori → group by name (pakai `select('categories.name', DB::raw('count(*) total'))` — portable kedua DB); `recent` = `latest('start_date')->latest('id')->take(5)`. Render `Dashboard` dengan props Interfaces.
- [ ] **Step 3 — UI.** `Dashboard.vue`: 4 `StatCard`; `BarChart` + `PieChart` (donut) — pakai `npx shadcn-vue@1 add chart` jika tersedia (menarik recharts), jika gagal: `npm install recharts` lalu impor `BarChart, Bar, XAxis...` langsung dari `recharts` dalam komponen kecil `resources/js/Components/charts/{MonthlyBar,CategoryDonut}.vue` (warnai `fill="var(--primary)"` agar ikut tema komunitas). Pemilih tahun `<Select>` → `router.get(url('dashboard'),{year})`. Daftar laporan terbaru (Link ke detail).
- [ ] **Step 4 — Ganti route dashboard:** `Route::get('dashboard', [DashboardController::class,'index'])->name('dashboard');` (hapus `Route::view`), hapus `placeholder.blade.php`.
- [ ] **Step 5 —** `php artisan test --filter=DashboardTest` → PASS; `npm run build`; smoke (grafik muncul, ganti tahun); commit `feat: dashboard statistik bertema komunitas`.

---

### Task 7: Manajemen Kategori (admin)

**Files:** Create `app/Http/Controllers/CategoryController.php`, `app/Policies/CategoryPolicy.php`, `resources/js/Pages/Categories/Index.vue`, `app/Http/Requests/StoreCategoryRequest.php` (+Update), `routes/web.php`; Test `tests/Feature/CategoryTest.php`

**Interfaces:**
- Consumes: alias middleware `admin`, `Category::for/optionsFor`, layout Task 5.
- Produces: route `community.categories.*` (resource tanpa show); guard: kategori yang dipakai laporan TIDAK bisa dihapus (FK `restrictOnDelete` → tangkap `QueryException` → flash error "Kategori masih dipakai laporan."); rename ok.

- [ ] **Step 1 — Test gagal:** anggota → GET/POST `/fad/categories` 403; admin FAD create → row `community=fad`; adminGenre tidak bisa list category FAD (index menampilkan hanya komunitasnya); delete kategori terpakai → flash error + row tetap; delete tak terpakai → hilang.
- [ ] **Step 2 — Controller+Policy** (policy: `admin` role + komunitas sama; tapi middleware `admin` sudah menolak non-admin — policy tetap memasang `where community` via query eksplisit seperti pola global). Validasi nama: `required|max:100` + unique per komunitas (`Rule::unique('categories')->where($q)->where('community', ...)`).
- [ ] **Step 3 — UI Index.vue:** tabel nama + jumlah laporan (`withCount('reports')`), form tambah inline (Input+Button), aksi edit (Dialog) & hapus (confirm Dialog). Menu sidebar sudah ada (Task 5, role-gated).
- [ ] **Step 4 —** test PASS, build, smoke, commit `feat: manajemen kategori per komunitas (admin)`.

---

### Task 8: Manajemen Pengguna (admin)

**Files:** Create `app/Http/Controllers/UserController.php`, `app/Policies/UserPolicy.php`, `app/Http/Requests/StoreUserRequest.php`, `app/Http/Requests/UpdateUserRequest.php`, `resources/js/Pages/Users/Index.vue`; Test `tests/Feature/UserManagementTest.php`

**Interfaces:**
- Consumes: `UserRole`, layout, flash.
- Produces: route `community.users.*` resource (index/store/update/destroy; `create` digabung Dialog di index). Aturan bisnis (WAJIB di test):
  - Semua aksi admin terscope komunitasnya (admin FAD tak lihat/ubah user GENRE → 404 via `findOrFail` + `where community`).
  - Admin tak bisa menghapus dirinya sendiri; tak bisa menghapus/menurunkan role admin terakhir komunitasnya (guard `last-admin`): `if ($target->isAdmin() && User::for($c)->where('role','admin')->count() <= 1 && $target->id === $user->id ...)`.
  - `email` unique global (Message: "Email sudah dipakai komunitas lain." bila milik komunitas berbeda — agar tak bocorkan? Tidak: pesan jujur cukup, email global unik).
  - Update: password boleh kosong = tidak diganti.

- [ ] **Step 1 — Test gagal** sesuai aturan di atas (min. 6 kasus: list scope, create, cross-community 404, self-delete 400+error, last-admin guard, update tanpa password).
- [ ] **Step 2 — Implementasi** controller + requests (`StoreUserRequest`: name required, email required email unique, password required confirmed min:8, role in:admin,anggota, community TIDAK diterima dari input — paksa `$request->user()->community`).
- [ ] **Step 3 — UI Users/Index.vue:** tabel (nama, email, role badge, jumlah laporan `withCount('reports')`, bergabung), Dialog tambah/edit (form select role), hapus dengan confirm; dialog reset-password dihilangkan — edit form punya kolom password opsional.
- [ ] **Step 4 —** test PASS, build, smoke, commit `feat: manajemen pengguna oleh admin komunitas`.

---

### Task 9: Export Excel filter-aktif

**Files:** Create `app/Exports/ReportExport.php`, modify `ReportController` (metode `export`), `routes/web.php`, `resources/js/Pages/Reports/Index.vue` (tombol)

**Interfaces:**
- Consumes: `Report::filtered` (query sama dengan index), maatwebsite/excel.
- Produces: GET `community/reports/export` (nama `community.reports.export`, letakkan SEBELUM `Route::resource` agar `{report}` tidak menelan "export") → download xlsx; kolom: `No, Judul, Kategori, Tanggal, Lokasi, Pelapor, Deskripsi (maks 200 char), Dibuat`; heading baris 1: `Laporan Proker {community.title}` + baris 2 rentang filter; implementasi `FromQuery` (chunk 200) + `WithHeadings` + `WithTitle`.

- [ ] **Step 1 —** `composer require maatwebsite/excel:^3.1`.
- [ ] **Step 2 — Test gagal** `tests/Feature/ReportExportTest.php`:

```php
public function test_export_honours_filters(): void
{
    // actingAs admin FAD; GET /fad/reports/export?month=3&year=2026
    // assert 200, content-type spreadsheet, dan (via excel assert atau cukup:
    //   query internal) jumlah baris = Report::filtered(FAD,['month'=>3,'year'=>2026])->count()
    // Sederhana & robust: stub ekspor — uji controller memanggil exporter dengan builder ter-filter:
}
```

Keputusan implementasi final: uji level HTTP `->assertOk()` + `assertDownload('laporan-fad-2026-03.xlsx')` (nama file dari controller: `laporan-{community}-{Y-m filter|semua}.xlsx`), plus unit test `ReportExport::map()` untuk 1 report → array kolom (deskripsi terpotong). TIDAK perlu parse xlsx di test.
- [ ] **Step 3 —** `export` di `ReportController`: pakai filter request yang sama dengan index → `new ReportExport($community, $filters)` → `->download($filename)`.
- [ ] **Step 4 —** tombol "Export Excel" di Index.vue: `<a :href="url('reports/export') + queryStringAktif">` — bangun query string dari filter aktif (`URLSearchParams`).
- [ ] **Step 5 —** test PASS, smoke (unduh file, buka, kolom benar), commit `feat: export excel mengikuti filter aktif`.

---

### Task 10: Hardening, README, final QA

**Files:** Create `README.md`, `tests/Feature/CrossCommunityTest.php`; modify secukupnya

- [ ] **Step 1 — Regression suite lintas komunitas** (matriks: login fad akses GET /genre/{dashboard,reports,categories,users} → redirect ke /fad/* ; session regenerate saat login; logout menghapus akses — 4 test).
- [ ] **Step 2 — QA pass full:** `php artisan test` (semua PASS), `npm run build`, `php artisan config:clear && php artisan migrate:fresh --seed`, smoke ulang alur lengkap kedua tema, cek `html[data-community]` per halaman, cek `storage:link` dibuat (`php artisan storage:link` — catat di README dev setup).
- [ ] **Step 3 — README:** setup (prasyarat PHP 8.3/Node, langkah Laragon, kredensial seed, ganti logo `public/images/*`, perintah dev/build/test, catatan backup `storage/`).
- [ ] **Step 4 — Commit final:** `docs: README + hardening isolasi komunitas`. Push ke remote bila user menyediakan URL repo (jangan invent).

---

## Verification Map

| Kriteria spec | Bukti |
|---|---|
| Dua tema + prefix | Task 2 tests + smoke Step Task 2 |
| Isolasi komunitas | Task 3 + Task 4 `test_genre_user_cannot_touch_fad_report` + Task 6 redirect test + Task 10 matriks |
| CRUD laporan + foto + policy | Task 4 |
| Dashboard grafik | Task 6 |
| Kategori & user admin-only | Task 7, 8 |
| Export sesuai filter | Task 9 |
| Build produksi | `npm run build` tiap task UI |
