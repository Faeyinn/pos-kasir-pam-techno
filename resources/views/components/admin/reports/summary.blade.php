<div class="mb-6">
    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
        <i data-lucide="filter" class="w-3 h-3"></i>
        <span>Filter Aktif</span>
    </div>
    <div class="text-sm font-semibold text-slate-600 bg-white px-4 py-2 rounded-xl border border-slate-100 shadow-sm inline-block" x-text="getActiveFiltersLabel()"></div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    {{-- Total Sales --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 flex-shrink-0">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Penjualan</p>
                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight" x-text="formatCurrency(summary.total_sales)"></h3>
            </div>
        </div>
    </div>

    {{-- Total Profit --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 flex-shrink-0">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Laba</p>
                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight" x-text="formatCurrency(summary.total_profit)"></h3>
            </div>
        </div>
    </div>

    {{-- Total Transactions --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 flex-shrink-0">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Transaksi</p>
                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight" x-text="summary.total_transactions"></h3>
            </div>
        </div>
    </div>

    {{-- Avg Transaction --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600 flex-shrink-0">
                <i data-lucide="calculator" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Rata-rata Transaksi</p>
                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight" x-text="formatCurrency(summary.avg_transaction)"></h3>
            </div>
        </div>
    </div>
</div>
