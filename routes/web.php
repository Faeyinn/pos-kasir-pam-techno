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
        Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products');
        Route::get('/discounts', [App\Http\Controllers\DiscountController::class, 'index'])->name('admin.discounts');
        Route::get('/reports', [App\Http\Controllers\LaporanController::class, 'index'])->name('admin.reports');
        Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
        Route::put('/users/{id}/role', [App\Http\Controllers\AdminController::class, 'updateUserRole'])->name('admin.users.update-role');
        Route::delete('/users/{id}', [App\Http\Controllers\AdminController::class, 'destroy'])->name('admin.users.destroy');
    });

    // Admin API Routes (outside admin group to be accessible at /api/admin/*)
    Route::prefix('api/admin')->middleware(['role:admin'])->group(function () {
        // Reports API
        Route::get('/reports/summary', [App\Http\Controllers\LaporanController::class, 'getSummary'])->name('api.admin.reports.summary');
        Route::get('/reports/charts', [App\Http\Controllers\LaporanController::class, 'getCharts'])->name('api.admin.reports.charts');
        Route::get('/reports/detail', [App\Http\Controllers\LaporanController::class, 'getDetail'])->name('api.admin.reports.detail');
        Route::get('/reports/export/csv', [App\Http\Controllers\LaporanController::class, 'exportCSV'])->name('api.admin.reports.export.csv');

        // Stats
        Route::get('/stats', [App\Http\Controllers\Api\AdminStatsController::class, 'stats'])->name('api.admin.stats');
        Route::get('/sales-profit-trend', [App\Http\Controllers\Api\AdminStatsController::class, 'salesProfitTrend'])->name('api.admin.sales-profit-trend');
        Route::get('/category-sales', [App\Http\Controllers\Api\AdminStatsController::class, 'categorySales'])->name('api.admin.category-sales');
        Route::get('/top-products', [App\Http\Controllers\Api\AdminStatsController::class, 'topProducts'])->name('api.admin.top-products');
        
        // Products CRUD
        Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('api.admin.products.store');
        Route::get('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'show'])->name('api.admin.products.show');
        Route::put('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('api.admin.products.update');
        Route::delete('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('api.admin.products.destroy');
        Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('api.admin.products.index');
        
        // Discounts CRUD
        Route::post('/discounts', [App\Http\Controllers\DiscountController::class, 'store'])->name('api.admin.discounts.store');
        Route::put('/discounts/{id}', [App\Http\Controllers\DiscountController::class, 'update'])->name('api.admin.discounts.update');
        Route::post('/discounts/{id}/toggle', [App\Http\Controllers\DiscountController::class, 'toggleStatus'])->name('api.admin.discounts.toggle');
        Route::delete('/discounts/{id}', [App\Http\Controllers\DiscountController::class, 'destroy'])->name('api.admin.discounts.destroy');
        
        // Heatmap Analytics
        Route::get('/heatmap/frequency', [App\Http\Controllers\Api\HeatmapController::class, 'getPurchaseFrequency'])->name('api.admin.heatmap.frequency');
        
        // Tags
        Route::get('/tags', [App\Http\Controllers\Api\TagController::class, 'index'])->name('api.tags');
    });

    // Kasir Routes
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir', function () {
            return view('pages.kasir');
        })->name('kasir');

        // API Routes for Kasir (assuming these should only be accessed by kasir/master-as-kasir)
        Route::prefix('api')->group(function () {
            Route::get('/tags', [App\Http\Controllers\Api\ProductController::class, 'getTags'])->name('api.tags.index');
            Route::get('/products', [App\Http\Controllers\Api\ProductController::class, 'index'])->name('api.products.index');
            Route::get('/products/{id}', [App\Http\Controllers\Api\ProductController::class, 'show'])->name('api.products.show');
            
            Route::get('/transactions', [App\Http\Controllers\Api\TransactionController::class, 'index'])->name('api.transactions.index');
            Route::post('/transactions', [App\Http\Controllers\Api\TransactionController::class, 'store'])->name('api.transactions.store');
            Route::get('/transactions/{id}', [App\Http\Controllers\Api\TransactionController::class, 'show'])->name('api.transactions.show');
        });
    });

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
});

