<?php

use App\Http\Controllers\KasirController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:kasir'])->group(function () {
    
    // Kasir Web Routes
    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir');

    // Kasir API Routes
    Route::prefix('api')->group(function () {
        
        // Tags for filtering
        Route::get('/tags', [ProductController::class, 'getTags'])->name('api.tags.index');
        
        // Products
        Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
        Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.products.show');
        
        // Transactions
        Route::get('/transactions', [TransactionController::class, 'index'])->name('api.transactions.index');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('api.transactions.store');
        Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('api.transactions.show');
    });
});
