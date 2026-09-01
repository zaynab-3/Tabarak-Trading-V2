<?php

use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\PublicMediaController;
use App\Http\Controllers\Storefront\ShopController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/shop')->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/search', [ShopController::class, 'index'])->name('search');
Route::get('/products/{product}', ProductController::class)->name('products.show');
Route::get('/media/{medium}', PublicMediaController::class)->name('media.show');
