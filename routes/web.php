<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Health check & Keep-alive endpoint (bypasses auth for pings)
Route::get('/health', fn() => response()->json([
    'status' => 'ok',
    'uptime' => 'healthy',
    'time' => now()->toIso8601String()
]));

// Redirect root to Admin Dashboard
Route::redirect('/', '/admin');

// Admin & Vendor Auth Routes (Guest)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::get('/register', [AdminAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Protected Dashboard Routes (Admin, Vendor, Staff)
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    // Overview
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/purge-cache', [DashboardController::class, 'purgeCache'])->name('purge-cache');

    // Products
    Route::resource('products', ProductController::class);

    // Banners
    Route::resource('banners', BannerController::class);

    // Categories
    Route::resource('categories', CategoryController::class);

    // Brands
    Route::resource('brands', BrandController::class);

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    // Multi-Vendor & User Permissions Management
    Route::resource('users', UserController::class);
});
