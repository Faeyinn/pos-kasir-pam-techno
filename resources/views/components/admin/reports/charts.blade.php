<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    {{-- Sales vs Profit Chart --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h4 class="text-lg font-bold text-slate-900 mb-4">Penjualan vs Laba</h4>
        <div class="relative h-72 w-full">
            <canvas id="salesProfitChart"></canvas>
        </div>
    </div>

    {{-- Profit by Tag --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h4 class="text-lg font-bold text-slate-900 mb-4">Distribusi Laba per Kategori</h4>
        <div class="relative h-72 w-full flex items-center justify-center">
            <canvas id="profitTagChart"></canvas>
        </div>
    </div>

    {{-- Transaction Trend --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm lg:col-span-2">
        <h4 class="text-lg font-bold text-slate-900 mb-4">Volume Transaksi Harian</h4>
        <div class="relative h-64 w-full">
            <canvas id="trxTrendChart"></canvas>
        </div>
    </div>
</div>
