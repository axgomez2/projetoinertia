<?php

use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VinylController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// O dashboard de usuário comum foi removido conforme solicitado.

// Rotas para Administradores (role 66)
Route::middleware(['auth', 'verified', 'role:66'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('vinyls/search-discogs', [VinylController::class, 'searchDiscogs'])->name('vinyls.searchDiscogs');
    Route::get('vinyls/discogs-details/{discogs_id}', [VinylController::class, 'getDiscogsDetails'])->name('vinyls.getDiscogsDetails');
    Route::resource('vinyls', VinylController::class);
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';

require __DIR__.'/auth.php';
