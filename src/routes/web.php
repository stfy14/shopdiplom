<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

// Публичные страницы
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

// Корзина
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');

    // Заказы
    Route::get('/checkout', [OrderController::class, 'index'])->name('checkout');
    Route::post('/checkout', [OrderController::class, 'store'])->name('order.store');
    Route::get('/orders/{uuid}', [OrderController::class, 'show'])->name('order.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('order.cancel');
    Route::get('/orders/{order}/messages', [OrderController::class, 'getMessages'])->name('order.messages');
    Route::post('/orders/{order}/messages', [OrderController::class, 'sendMessage'])->name('order.send-message');
    Route::patch('/orders/{order}/contacts', [OrderController::class, 'updateContacts'])->name('order.contacts');

    //Профиль
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
});

// Админка
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\ProductController::class, 'index'])->name('products');
    Route::get('/products/create', [Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [Admin\ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/products/{product}/restore', [Admin\ProductController::class, 'restore'])->name('products.restore');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders/{order}/messages', [Admin\OrderController::class, 'getMessages'])->name('orders.messages');
    Route::post('/orders/{order}/messages', [Admin\OrderController::class, 'sendMessage'])->name('orders.send-message');

    Route::get('/categories', [Admin\CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/characteristics', [Admin\CharacteristicController::class, 'index'])->name('characteristics');
    Route::post('/characteristics', [Admin\CharacteristicController::class, 'store'])->name('characteristics.store');
    Route::delete('/characteristics/{characteristic}', [Admin\CharacteristicController::class, 'destroy'])->name('characteristics.destroy');
});

require __DIR__.'/auth.php';