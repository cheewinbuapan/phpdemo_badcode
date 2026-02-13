<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    // Authentication routes with rate limiting (5 attempts per minute)
    Route::middleware(['throttle:5,1'])->group(function () {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store']);
        
        Route::get('/register', [RegisterController::class, 'create'])->name('register');
        Route::post('/register', [RegisterController::class, 'store']);
    });
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    
    // Welcome/home redirect
    Route::get('/', function () {
        return redirect()->route('products.index');
    });
    
    // Product routes
    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])
        ->name('products.index');
    
    // Order routes
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])
        ->name('orders.index');
    Route::get('/orders/create', [\App\Http\Controllers\OrderController::class, 'create'])
        ->name('orders.create');
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])
        ->name('orders.store');
    Route::get('/orders/{orderNumber}', [\App\Http\Controllers\OrderController::class, 'show'])
        ->name('orders.show');
    Route::get('/orders/{orderNumber}/edit', [\App\Http\Controllers\OrderController::class, 'edit'])
        ->name('orders.edit');
    Route::put('/orders/{orderNumber}', [\App\Http\Controllers\OrderController::class, 'update'])
        ->name('orders.update');
    Route::get('/orders/{orderNumber}/confirm', [\App\Http\Controllers\OrderController::class, 'showConfirmForm'])
        ->name('orders.confirm.form');
    Route::post('/orders/{orderNumber}/confirm', [\App\Http\Controllers\OrderController::class, 'confirm'])
        ->name('orders.confirm');
    
    // Admin routes (protected by admin middleware)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');
        
        Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])
            ->name('orders.index');
        Route::post('/orders/bulk-confirm', [\App\Http\Controllers\Admin\OrderController::class, 'bulkConfirm'])
            ->name('orders.bulk-confirm');
    });
});
