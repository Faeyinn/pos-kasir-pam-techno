<div class="bg-gradient-to-br from-slate-50 to-white rounded-xl border-2 border-slate-200 p-8">
    <h3 class="text-lg font-semibold text-slate-900 mb-6 text-center">
        Perbandingan Performa Penjualan (30 Hari Terakhir)
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- WITHOUT Discount Card --}}
        <div class="bg-white border-2 border-red-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-slate-900">Tanpa Diskon</h4>
                <span class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="text-red-600 text-xl">•</span>
                </span>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-xs text-slate-500">Rata-rata Belanja per Nota</p>
                    <p class="text-2xl font-bold text-slate-900" 
                       x-text="'Rp ' + formatNumber(comparison.without_discount?.avg_transaction || 0)">
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Total Penjualan (Omzet)</p>
                    <p class="text-2xl font-bold text-slate-900"
                       x-text="'Rp ' + formatNumber(comparison.without_discount?.total_revenue || 0)">
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Keuntungan Bersih (Laba)</p>
                    <p class="text-2xl font-bold text-slate-900"
                       x-text="'Rp ' + formatNumber(comparison.without_discount?.total_profit || 0)">
                    </p>
                    <p class="text-xs text-slate-500"
                       x-text="'(' + (comparison.without_discount?.profit_margin || 0) + '% margin)'">
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Jumlah Transaksi</p>
                    <p class="text-2xl font-bold text-slate-900"
                       x-text="(comparison.without_discount?.transaction_count || 0) + ' transaksi'">
                    </p>
                </div>
            </div>
        </div>

        {{-- WITH Discount Card --}}
        <div class="bg-white border-2 border-green-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-slate-900">Dengan Diskon</h4>
                <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-green-600 text-xl">•</span>
                </span>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-xs text-slate-500">Rata-rata Belanja per Nota</p>
                    <p class="text-2xl font-bold text-slate-900"
                       x-text="'Rp ' + formatNumber(comparison.with_discount?.avg_transaction || 0)">
                    </p>
                    <div class="text-sm font-medium" x-html="formatDiff(comparison.diff?.avg_transaction || 0, 'high')"></div>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Total Penjualan (Omzet)</p>
                    <p class="text-2xl font-bold text-slate-900"
                       x-text="'Rp ' + formatNumber(comparison.with_discount?.total_revenue || 0)">
                    </p>
                    <div class="text-sm font-medium" x-html="formatDiff(comparison.diff?.total_revenue || 0, 'high')"></div>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Keuntungan Bersih (Laba)</p>
                    <p class="text-2xl font-bold text-slate-900"
                       x-text="'Rp ' + formatNumber(comparison.with_discount?.total_profit || 0)">
                    </p>
                    <p class="text-xs text-slate-500"
                       x-text="'(' + (comparison.with_discount?.profit_margin || 0) + '% margin)'">
                    </p>
                    <div class="text-sm font-medium" x-html="formatDiff(comparison.diff?.total_profit || 0, 'high')"></div>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Jumlah Transaksi</p>
                    <p class="text-2xl font-bold text-slate-900"
                       x-text="(comparison.with_discount?.transaction_count || 0) + ' transaksi'">
                    </p>
                    <div class="text-sm font-medium" x-html="formatDiff(comparison.diff?.transaction_count || 0, 'count')"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Conclusion Box --}}
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-start gap-3">
            <i data-lucide="lightbulb" class="w-5 h-5 text-blue-600 mt-0.5"></i>
            <div>
                <p class="font-semibold text-blue-900">Kesimpulan</p>
                <p class="text-sm text-blue-800 mt-1" x-html="getConclusion()"></p>
            </div>
        </div>
    </div>
</div>
