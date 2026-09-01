<?php

use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\OrderController;
use App\Http\Controllers\Storefront\OrderItemImageController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\PublicMediaController;
use App\Http\Controllers\Storefront\ShopController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/shop')->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/search', [ShopController::class, 'index'])->name('search');
Route::get('/products/{product}', ProductController::class)->name('products.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items/{product}', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{product}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1')->name('orders.store');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{order}/invoice.pdf', [OrderController::class, 'invoice'])->middleware('throttle:30,1')->name('orders.invoice');
Route::get('/orders/{order}/items/{orderItem}/image', OrderItemImageController::class)->name('orders.items.image');
Route::get('/media/{medium}', PublicMediaController::class)->name('media.show');
