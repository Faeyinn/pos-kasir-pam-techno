<?php

use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/kasir', function () {
        return view('pages.kasir');
    })->name('kasir');

    // API Routes for Kasir
    Route::prefix('api')->group(function () {
        Route::get('/products', [App\Http\Controllers\Api\ProductController::class, 'index'])->name('api.products.index');
        Route::get('/products/{id}', [App\Http\Controllers\Api\ProductController::class, 'show'])->name('api.products.show');
        
        Route::get('/transactions', [App\Http\Controllers\Api\TransactionController::class, 'index'])->name('api.transactions.index');
        Route::post('/transactions', [App\Http\Controllers\Api\TransactionController::class, 'store'])->name('api.transactions.store');
        Route::get('/transactions/{id}', [App\Http\Controllers\Api\TransactionController::class, 'show'])->name('api.transactions.show');
    });
});

