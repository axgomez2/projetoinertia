<?php

use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VinylController;
use App\Http\Controllers\Admin\YouTubeController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\OnlineOrderController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CustomerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// O dashboard de usuário comum foi removido conforme solicitado.

// Rotas para Administradores (role 66)
Route::middleware(['auth', 'verified', 'role:66'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('vinyls/search-discogs', [VinylController::class, 'searchDiscogs'])->name('vinyls.searchDiscogs');
    Route::get('vinyls/discogs-details/{discogs_id}', [VinylController::class, 'getDiscogsDetails'])->name('vinyls.getDiscogsDetails');
    Route::get('vinyls/{id}/complete', [VinylController::class, 'complete'])->name('vinyls.complete');
    Route::post('vinyls/{id}/complete', [VinylController::class, 'completeStore'])->name('vinyls.completeStore');
    Route::post('vinyls/bulk-action', [VinylController::class, 'bulkAction'])->name('vinyls.bulkAction');
    Route::get('vinyls/export', [VinylController::class, 'export'])->name('vinyls.export');
    Route::post('vinyls/{vinyl}/update-image', [VinylController::class, 'updateImage'])->name('vinyls.updateImage');
    Route::resource('vinyls', VinylController::class);
    Route::get('/youtube/search', [YouTubeController::class, 'search'])->name('youtube.search');

    // Online Orders routes
    Route::prefix('orders/online')->name('orders.online.')->group(function () {
        Route::get('/', [OnlineOrderController::class, 'index'])->name('index');
        Route::get('/export', [OnlineOrderController::class, 'export'])->name('export');
        Route::get('/stats', [OnlineOrderController::class, 'stats'])->name('stats');
        Route::get('/{order}', [OnlineOrderController::class, 'show'])->name('show');
    });

    // POS (Point of Sale) routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/create', [POSController::class, 'create'])->name('create');
        Route::post('/', [POSController::class, 'store'])->name('store');
        Route::get('/search-products', [POSController::class, 'searchProducts'])->name('search-products');
        Route::get('/{posSale}', [POSController::class, 'show'])->name('show');
        Route::get('/{posSale}/receipt', [POSController::class, 'receipt'])->name('receipt');
    });

    // Settings routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
        Route::get('/test', function() {
            return Inertia::render('Admin/Settings/Test');
        })->name('test');

        // Categories routes
        Route::get('/categories', [\App\Http\Controllers\Admin\SettingsController::class, 'categories'])->name('categories');
        Route::post('/categories', [\App\Http\Controllers\Admin\SettingsController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [\App\Http\Controllers\Admin\SettingsController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\SettingsController::class, 'destroyCategory'])->name('categories.destroy');

        // Media Status routes
        Route::get('/media-status', [\App\Http\Controllers\Admin\SettingsController::class, 'mediaStatus'])->name('media-status');
        Route::post('/media-status', [\App\Http\Controllers\Admin\SettingsController::class, 'storeMediaStatus'])->name('media-status.store');
        Route::put('/media-status/{mediaStatus}', [\App\Http\Controllers\Admin\SettingsController::class, 'updateMediaStatus'])->name('media-status.update');
        Route::delete('/media-status/{mediaStatus}', [\App\Http\Controllers\Admin\SettingsController::class, 'destroyMediaStatus'])->name('media-status.destroy');

        // Cover Status routes
        Route::get('/cover-status', [\App\Http\Controllers\Admin\SettingsController::class, 'coverStatus'])->name('cover-status');
        Route::post('/cover-status', [\App\Http\Controllers\Admin\SettingsController::class, 'storeCoverStatus'])->name('cover-status.store');
        Route::put('/cover-status/{coverStatus}', [\App\Http\Controllers\Admin\SettingsController::class, 'updateCoverStatus'])->name('cover-status.update');
        Route::delete('/cover-status/{coverStatus}', [\App\Http\Controllers\Admin\SettingsController::class, 'destroyCoverStatus'])->name('cover-status.destroy');

        // Suppliers routes
        Route::get('/suppliers', [\App\Http\Controllers\Admin\SettingsController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers', [\App\Http\Controllers\Admin\SettingsController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSupplier'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [\App\Http\Controllers\Admin\SettingsController::class, 'destroySupplier'])->name('suppliers.destroy');
    });

    // Reports routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/vinyls', [ReportController::class, 'vinyls'])->name('vinyls');
        Route::get('/cart-items', [ReportController::class, 'cartItems'])->name('cart-items');
        Route::get('/wishlist', [ReportController::class, 'wishlist'])->name('wishlist');
        Route::get('/wantlist', [ReportController::class, 'wantlist'])->name('wantlist');
        Route::get('/product-views', [ReportController::class, 'productViews'])->name('product-views');
        Route::get('/supplier-inventory', [ReportController::class, 'supplierInventory'])->name('supplier-inventory');
    });

    // Customer Management routes
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('index');
        Route::get('/export', [\App\Http\Controllers\Admin\CustomerController::class, 'export'])->name('export');
        Route::get('/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('show');
        Route::post('/{customer}/notes', [\App\Http\Controllers\Admin\CustomerController::class, 'storeNote'])->name('notes.store');
        Route::post('/{customer}/communications', [\App\Http\Controllers\Admin\CustomerController::class, 'storeCommunication'])->name('communications.store');
        Route::patch('/{customer}/status', [\App\Http\Controllers\Admin\CustomerController::class, 'updateStatus'])->name('status.update');
    });
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';

require __DIR__.'/auth.php';
