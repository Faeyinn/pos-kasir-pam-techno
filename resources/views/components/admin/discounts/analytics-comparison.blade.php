<div class="relative min-h-[400px]">
    {{-- Main Comparison Content (Only show if there are transactions with discount) --}}
    <template x-if="comparison.with_discount?.transaction_count > 0">
        <div class="bg-gradient-to-br from-slate-50 to-white rounded-xl border-2 border-slate-200 p-8 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900 mb-6 text-center">
                Perbandingan Performa Penjualan (30 Hari Terakhir)
            </h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- WITHOUT Discount Card --}}
                <div class="bg-white border-2 border-red-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-semibold text-slate-900">Tanpa Diskon</h4>
                        <span class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-red-600 text-xl">•</span>
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Rata-rata Belanja per Nota</p>
                            <p class="text-2xl font-black text-slate-900" 
                            x-text="'Rp ' + formatNumber(comparison.without_discount?.avg_transaction || 0)">
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Total Penjualan (Omzet)</p>
                            <p class="text-2xl font-black text-slate-900"
                            x-text="'Rp ' + formatNumber(comparison.without_discount?.total_revenue || 0)">
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Keuntungan Bersih (Laba)</p>
                            <p class="text-2xl font-black text-slate-900"
                            x-text="'Rp ' + formatNumber(comparison.without_discount?.total_profit || 0)">
                            </p>
                            <p class="text-xs text-slate-500 font-medium"
                            x-text="'(' + (comparison.without_discount?.profit_margin || 0) + '% margin)'">
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Jumlah Transaksi</p>
                            <p class="text-2xl font-black text-slate-900"
                            x-text="(comparison.without_discount?.transaction_count || 0) + ' transaksi'">
                            </p>
                        </div>
                    </div>
                </div>

                {{-- WITH Discount Card --}}
                <div class="bg-white border-2 border-green-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-semibold text-slate-900">Dengan Diskon</h4>
                        <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-green-600 text-xl">•</span>
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Rata-rata Belanja per Nota</p>
                            <p class="text-2xl font-black text-slate-900"
                            x-text="'Rp ' + formatNumber(comparison.with_discount?.avg_transaction || 0)">
                            </p>
                            <div class="text-sm font-medium mt-1" x-html="formatDiff(comparison.diff?.avg_transaction || 0, 'high')"></div>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Total Penjualan (Omzet)</p>
                            <p class="text-2xl font-black text-slate-900"
                            x-text="'Rp ' + formatNumber(comparison.with_discount?.total_revenue || 0)">
                            </p>
                            <div class="text-sm font-medium mt-1" x-html="formatDiff(comparison.diff?.total_revenue || 0, 'high')"></div>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Keuntungan Bersih (Laba)</p>
                            <p class="text-2xl font-black text-slate-900"
                            x-text="'Rp ' + formatNumber(comparison.with_discount?.total_profit || 0)">
                            </p>
                            <p class="text-xs text-slate-500 font-medium"
                            x-text="'(' + (comparison.with_discount?.profit_margin || 0) + '% margin)'">
                            </p>
                            <div class="text-sm font-medium mt-1" x-html="formatDiff(comparison.diff?.total_profit || 0, 'high')"></div>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Jumlah Transaksi</p>
                            <p class="text-2xl font-black text-slate-900"
                            x-text="(comparison.with_discount?.transaction_count || 0) + ' transaksi'">
                            </p>
                            <div class="text-sm font-medium mt-1" x-html="formatDiff(comparison.diff?.transaction_count || 0, 'count')"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Conclusion Box --}}
            <div class="mt-6 p-5 bg-blue-50 border border-blue-200 rounded-xl shadow-inner">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                        <i data-lucide="lightbulb" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="font-bold text-blue-900 uppercase tracking-tight">Analisis Strategis</p>
                        <p class="text-sm text-blue-800 mt-1 leading-relaxed" x-html="getConclusion()"></p>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Empty State (Show if no transactions with discount) --}}
    <template x-if="!(comparison.with_discount?.transaction_count > 0)">
        <div 
            class="bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center"
            style="margin-top: 80px; padding: 15px 40px;"
        >
            <div class="w-20 h-20 bg-white rounded-3xl shadow-sm border border-slate-100 flex items-center justify-center mb-8">
                <i data-lucide="bar-chart-2" class="w-10 h-10 text-slate-300"></i>
            </div>
            <h3 class="text-xl font-black text-slate-400 uppercase tracking-tight">Fitur Analisis Belum Tersedia</h3>
            <p class="text-slate-400 mt-4 max-w-sm font-medium leading-relaxed">
                Fitur ini belum tersedia karena tidak ada transaksi dengan diskon yang ditemukan selama 30 hari terakhir dalam data Anda.
            </p>
            <div class="mt-12 px-8 py-3 bg-slate-200/50 rounded-full text-[10px] font-bold text-slate-400 uppercase tracking-widest border border-slate-300/30">
                Menunggu Data Transaksi Ber-Diskon
            </div>
        </div>
    </template>
</div>
