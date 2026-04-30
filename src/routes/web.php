<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

// ── Публичные страницы ──────────────────────────────────────────────────────
Route::get('/', fn() => inertia('Welcome'))->name('home');

// Каталог
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Поиск / все товары
Route::get('/search', [ProductController::class, 'index'])->name('search');

// Страница товара
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

// Запрос на звонок (публичный)
Route::post('/callback', [CallbackController::class, 'store'])->name('callback.store');

// ── Корзина и заказы (auth) ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Уведомления
    Route::post('/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markRead');
    Route::delete('/notifications/all', [\App\Http\Controllers\NotificationController::class, 'destroyAll'])->name('notifications.destroyAll');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Корзина
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::patch('/cart/{product}/clear-notification', [CartController::class, 'clearNotification'])->name('cart.clear-notification');

    // Заказы
    Route::get('/checkout', [OrderController::class, 'index'])->name('checkout');
    Route::post('/checkout', [OrderController::class, 'store'])->name('order.store');
    Route::get('/orders/{uuid}', [OrderController::class, 'show'])->name('order.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('order.cancel');
    Route::get('/orders/{order}/messages', [OrderController::class, 'getMessages'])->name('order.messages');
    Route::post('/orders/{order}/messages', [OrderController::class, 'sendMessage'])->name('order.send-message');
    Route::patch('/orders/{order}/contacts', [OrderController::class, 'updateContacts'])->name('order.contacts');
    Route::post('/orders/{order}/read', [OrderController::class, 'readMessages'])->name('order.read');

    // Профиль
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
});

// ── Админка ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Товары
    Route::get('/products', [Admin\ProductController::class, 'index'])->name('products');
    Route::get('/products/create', [Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [Admin\ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/products/{product}/restore', [Admin\ProductController::class, 'restore'])->name('products.restore');

    // Заказы
    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders/{order}/messages', [Admin\OrderController::class, 'getMessages'])->name('orders.messages');
    Route::post('/orders/{order}/messages', [Admin\OrderController::class, 'sendMessage'])->name('orders.send-message');
    Route::patch('/orders/{order}/contacts', [Admin\OrderController::class, 'updateContacts'])->name('orders.contacts');

    // Запросы на звонок
    Route::get('/callbacks', [Admin\CallbackController::class, 'index'])->name('callbacks');
    Route::patch('/callbacks/{callback}/processed', [Admin\CallbackController::class, 'markProcessed'])->name('callbacks.processed');
    Route::delete('/callbacks/{callback}', [Admin\CallbackController::class, 'destroy'])->name('callbacks.destroy');

    // Категории
    Route::get('/categories', [Admin\CategoryController::class, 'index'])->name('categories');
    Route::post('/categories/parent', [Admin\CategoryController::class, 'storeParent'])->name('categories.store-parent');
    Route::post('/categories/child', [Admin\CategoryController::class, 'storeChild'])->name('categories.store-child');
    Route::put('/categories/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Характеристики
    Route::get('/characteristics', [Admin\CharacteristicController::class, 'index'])->name('characteristics');
    Route::post('/characteristics', [Admin\CharacteristicController::class, 'store'])->name('characteristics.store');
    Route::delete('/characteristics/{characteristic}', [Admin\CharacteristicController::class, 'destroy'])->name('characteristics.destroy');
});

require __DIR__ . '/auth.php';