<?php

use App\Http\Controllers\Admin\AdminSessionController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImportBatchController;
use App\Http\Controllers\Admin\ImportBatchImageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminSessionController::class, 'create'])->name('login');
        Route::post('/login', [AdminSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminSessionController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('products', ProductController::class)->except(['show', 'destroy']);
        Route::patch('/products/{product}/archive', [ProductController::class, 'archive'])->name('products.archive');
        Route::patch('/products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
        Route::post('/products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
        Route::delete('/products/{product}/images/{productImage}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::patch('/products/{product}/images/{productImage}/primary', [ProductImageController::class, 'primary'])->name('products.images.primary');

        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update']);
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::resource('brands', BrandController::class)->only(['index', 'store', 'update']);
        Route::patch('/brands/{brand}/toggle', [BrandController::class, 'toggle'])->name('brands.toggle');

        Route::resource('media', MediaController::class)->only(['index', 'store', 'destroy']);
        Route::resource('imports', ImportBatchController::class)->parameters(['imports' => 'importBatch'])->only(['index', 'store', 'show']);
        Route::post('/imports/{importBatch}/images', [ImportBatchImageController::class, 'store'])->name('imports.images.store');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
