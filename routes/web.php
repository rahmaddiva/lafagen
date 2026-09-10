<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Landing')->name('landing');
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
            Route::view('dashboard', 'placeholder')->name('dashboard'); // diganti Task 6
        });
    });
