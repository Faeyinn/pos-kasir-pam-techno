<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AdminStatsController;
use App\Http\Controllers\Api\DiscountAnalyticsController;
use App\Http\Controllers\Api\HeatmapController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    
    // Admin Web Routes
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/products', [ProductController::class, 'index'])->name('admin.products');
        Route::get('/discounts', [DiscountController::class, 'index'])->name('admin.discounts');
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
        
        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::put('/users/{id}/role', [UserController::class, 'updateRole'])->name('admin.users.update-role');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });

    // Admin API Routes
    Route::prefix('api/admin')->group(function () {
        
        // Dashboard Stats
        Route::get('/stats', [AdminStatsController::class, 'stats'])->name('api.admin.stats');
        Route::get('/sales-profit-trend', [AdminStatsController::class, 'salesProfitTrend'])->name('api.admin.sales-profit-trend');
        Route::get('/category-sales', [AdminStatsController::class, 'categorySales'])->name('api.admin.category-sales');
        Route::get('/top-products', [AdminStatsController::class, 'topProducts'])->name('api.admin.top-products');
        Route::get('/recent-transactions', [AdminStatsController::class, 'recentTransactions'])->name('api.admin.recent-transactions');
        
        // Reports API
        Route::get('/reports/summary', [ReportController::class, 'getSummary'])->name('api.admin.reports.summary');
        Route::get('/reports/charts', [ReportController::class, 'getCharts'])->name('api.admin.reports.charts');
        Route::get('/reports/detail', [ReportController::class, 'getDetail'])->name('api.admin.reports.detail');
        Route::get('/reports/export/csv', [ReportController::class, 'exportCSV'])->name('api.admin.reports.export.csv');
        
        // Products CRUD
        Route::get('/products', [ProductController::class, 'index'])->name('api.admin.products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('api.admin.products.store');
        Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.admin.products.show');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('api.admin.products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('api.admin.products.destroy');
        
        // Discounts CRUD
        Route::post('/discounts', [DiscountController::class, 'store'])->name('api.admin.discounts.store');
        Route::put('/discounts/{id}', [DiscountController::class, 'update'])->name('api.admin.discounts.update');
        Route::post('/discounts/{id}/toggle', [DiscountController::class, 'toggleStatus'])->name('api.admin.discounts.toggle');
        Route::delete('/discounts/{id}', [DiscountController::class, 'destroy'])->name('api.admin.discounts.destroy');
        
        // Discount Analytics
        Route::get('/discounts/analytics', [DiscountAnalyticsController::class, 'index'])->name('api.admin.discounts.analytics');
        
        // Heatmap Analytics
        Route::get('/heatmap/frequency', [HeatmapController::class, 'getPurchaseFrequency'])->name('api.admin.heatmap.frequency');
        
        // Tags
        Route::get('/tags', [TagController::class, 'index'])->name('api.admin.tags');
        Route::post('/tags', [TagController::class, 'store'])->name('api.admin.tags.store');
        Route::put('/tags/{id}', [TagController::class, 'update'])->name('api.admin.tags.update');
        Route::delete('/tags/{id}', [TagController::class, 'destroy'])->name('api.admin.tags.destroy');
    });
});
