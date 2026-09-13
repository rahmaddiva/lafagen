<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // Satu sumber kebenaran identitas komunitas: config/communities.php
    // (nama, singkatan, logo) + statistik langsung dari basis data.
    $communities = collect(config('communities'))
        ->map(fn (array $c, string $key) => [
            'key' => $key,
            'short' => $c['short'],
            'name' => $c['name'],
            'title' => $c['title'],
            'logo' => $c['logo'],
            'members' => \App\Models\User::where('community', $key)->count(),
            'reports' => \App\Models\Report::where('community', $key)->count(),
        ])
        ->values()
        ->all();

    return Inertia::render('Landing', [
        'title' => 'Lafagen',
        'communities' => $communities,
    ]);
})->name('landing');
Route::prefix('{community}')
    ->where(['community' => 'fad|genre'])
    ->middleware('community')
    ->name('community.')
    ->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])
            ->middleware('throttle:10,1')->name('login.store');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::middleware('auth')->group(function () {
            Route::get('/', fn (\Illuminate\Http\Request $r) => redirect('/'.$r->route('community').'/dashboard'));
            Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
            Route::get('reports/export', [\App\Http\Controllers\ReportController::class, 'export'])
                ->name('reports.export');
            Route::resource('reports', \App\Http\Controllers\ReportController::class);

            Route::middleware('admin')->group(function () {
                Route::resource('categories', \App\Http\Controllers\CategoryController::class)
                    ->except(['create', 'show', 'edit']);
                Route::resource('users', \App\Http\Controllers\UserController::class)
                    ->except(['create', 'show', 'edit']);
            });
        });
    });
