<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden no-print">
    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h4 class="text-lg font-bold text-slate-900">Laporan Detail Penjualan</h4>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1" x-text="getActiveFiltersLabel()"></p>
        </div>
        <div class="relative w-full sm:w-64">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4"></i>
            <input 
                type="text" 
                x-model.debounce.500ms="filters.search"
                class="w-full pl-9 pr-4 py-2 bg-slate-50 border-none rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 placeholder:text-slate-400"
                placeholder="Cari produk atau no trx..."
            >
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">No Transaksi</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Produk</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Qty</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Harga Jual</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Modal</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Laba</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Metode</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-if="detailLoading">
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"></div>
                        </td>
                    </tr>
                </template>
                
                <template x-for="row in detailData" :key="row.transaction_number + row.product_name">
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-600" x-text="formatDate(row.created_at)"></td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900" x-text="row.transaction_number"></td>
                        <td class="px-6 py-4 text-sm text-slate-900" x-text="row.product_name"></td>
                        <td class="px-6 py-4 text-sm text-slate-900 text-right" x-text="row.qty"></td>
                        <td class="px-6 py-4 text-sm text-slate-600 text-right" x-text="formatCurrency(row.selling_price)"></td>
                        <td class="px-6 py-4 text-sm text-slate-400 text-right" x-text="formatCurrency(row.cost_price)"></td>
                        <td class="px-6 py-4 text-sm font-semibold text-emerald-600 text-right" x-text="formatCurrency(row.profit)"></td>
                        <td class="px-6 py-4 text-center">
                            <span 
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                :class="{
                                    'bg-emerald-100 text-emerald-700': (row.payment_method ?? row.payment_type) === 'tunai',
                                    'bg-indigo-100 text-indigo-700': (row.payment_method ?? row.payment_type) === 'kartu',
                                    'bg-purple-100 text-purple-700': (row.payment_method ?? row.payment_type) === 'qris',
                                    'bg-amber-100 text-amber-700': (row.payment_method ?? row.payment_type) === 'ewallet',
                                    'bg-slate-100 text-slate-700': !['tunai','kartu','qris','ewallet'].includes(row.payment_method ?? row.payment_type)
                                }"
                                x-text="(row.payment_method ?? row.payment_type) === 'tunai' ? 'Tunai' : (row.payment_method ?? row.payment_type) === 'kartu' ? 'Kartu' : (row.payment_method ?? row.payment_type) === 'qris' ? 'QRIS' : (row.payment_method ?? row.payment_type) === 'ewallet' ? 'E-Wallet' : ((row.payment_method ?? row.payment_type) || '-')"
                            ></span>
                        </td>
                    </tr>
                </template>

                <template x-if="!detailLoading && detailData.length === 0">
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            Tidak ada data ditemukan
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="p-4 border-t border-slate-100 flex items-center justify-between" x-show="pagination.total > 0">
        <span class="text-sm text-slate-500">
            Show <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> entries
        </span>
        <div class="flex gap-2">
            <button 
                @click="changePage(pagination.current_page - 1)" 
                :disabled="pagination.current_page === 1"
                class="px-3 py-1 border border-slate-200 rounded text-sm disabled:opacity-50 hover:bg-slate-50"
            >
                Prev
            </button>
            <button 
                @click="changePage(pagination.current_page + 1)" 
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-1 border border-slate-200 rounded text-sm disabled:opacity-50 hover:bg-slate-50"
            >
                Next
            </button>
        </div>
    </div>
</div>
