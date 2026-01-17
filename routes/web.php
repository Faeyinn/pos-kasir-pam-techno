<?php

use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    
    // Role Selection for Master
    Route::get('/role-selection', [App\Http\Controllers\AdminController::class, 'roleSelection'])->name('role.selection');
    Route::post('/role-selection', [App\Http\Controllers\AdminController::class, 'setRole'])->name('role.set');

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/products', [App\Http\Controllers\AdminController::class, 'products'])->name('admin.products');
        Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    });

    // Kasir Routes
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir', function () {
            return view('pages.kasir');
        })->name('kasir');

        // API Routes for Kasir (assuming these should only be accessed by kasir/master-as-kasir)
        Route::prefix('api')->group(function () {
            Route::get('/products', [App\Http\Controllers\Api\ProductController::class, 'index'])->name('api.products.index');
            Route::get('/products/{id}', [App\Http\Controllers\Api\ProductController::class, 'show'])->name('api.products.show');
            
            Route::get('/transactions', [App\Http\Controllers\Api\TransactionController::class, 'index'])->name('api.transactions.index');
            Route::post('/transactions', [App\Http\Controllers\Api\TransactionController::class, 'store'])->name('api.transactions.store');
            Route::get('/transactions/{id}', [App\Http\Controllers\Api\TransactionController::class, 'show'])->name('api.transactions.show');
        });
    });

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
});

