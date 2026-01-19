@props(['loading' => false])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl p-6 border border-slate-100 shadow-sm']) }}>
    <div class="mb-6">
        <h3 class="text-lg font-bold text-slate-900">Tren Penjualan & Laba</h3>
        <p class="text-sm text-slate-500 mt-1">7 Hari Terakhir</p>
    </div>
    <div class="relative" style="height: 300px;">
        <template x-if="loading">
            <div class="absolute inset-0 bg-slate-50 rounded-xl animate-pulse"></div>
        </template>
        <canvas id="salesProfitChart" x-show="!loading"></canvas>
    </div>
</div>
