@extends('layouts.admin')
@section('header', 'Dashboard')
@section('content')
<div x-data="adminDashboard()" x-init="init()" class="space-y-6">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-admin.dashboard.kpi-card 
            title="Penjualan Hari Ini"
            icon="trending-up"
            color="blue"
            :loading="false"
            x-text="formatRupiah(stats.sales_today)"
        />
        
        <x-admin.dashboard.kpi-card 
            title="Laba Hari Ini"
            icon="dollar-sign"
            color="green"
            :loading="false"
            x-text="formatRupiah(stats.profit_today)"
        />
        
        <x-admin.dashboard.kpi-card 
            title="Transaksi Hari Ini"
            icon="shopping-cart"
            color="purple"
            :loading="false"
            x-text="stats.transactions_today"
        />
        
        <x-admin.dashboard.kpi-card 
            title="Produk Stok Menipis"
            icon="alert-triangle"
            color="red"
            :loading="false"
            x-text="stats.low_stock_count"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-admin.dashboard.sales-profit-chart 
            class="lg:col-span-2" 
            :loading="false" 
        />
        
        <x-admin.dashboard.category-chart 
            :loading="false" 
        />
    </div>

    <x-admin.dashboard.recent-transactions-table 
        :loading="false" 
    />

    <x-admin.dashboard.top-products-table 
        :loading="false" 
    />
</div>

@push('scripts')
    <x-admin.scripts.dashboard />
@endpush
@endsection
