<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    {{-- Total Sales --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total Penjualan</p>
                <h3 class="text-2xl font-bold text-slate-900" x-text="formatCurrency(summary.total_sales)"></h3>
            </div>
        </div>
    </div>

    {{-- Total Profit --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                <i data-lucide="trending-up" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total Laba</p>
                <h3 class="text-2xl font-bold text-slate-900" x-text="formatCurrency(summary.total_profit)"></h3>
            </div>
        </div>
    </div>

    {{-- Total Transactions --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                <i data-lucide="shopping-bag" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total Transaksi</p>
                <h3 class="text-2xl font-bold text-slate-900" x-text="summary.total_transactions"></h3>
            </div>
        </div>
    </div>

    {{-- Avg Transaction --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600">
                <i data-lucide="calculator" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Rata-rata Transaksi</p>
                <h3 class="text-2xl font-bold text-slate-900" x-text="formatCurrency(summary.avg_transaction)"></h3>
            </div>
        </div>
    </div>
</div>
