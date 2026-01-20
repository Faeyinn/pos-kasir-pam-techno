@props(['loading' => false])

<div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Riwayat Transaksi Terakhir</h3>
            <p class="text-sm text-slate-500 mt-1">Aktivitas penjualan terbaru</p>
        </div>
        <a href="{{ route('admin.reports') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
            Lihat Semua
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left border-b border-slate-50">
                    <th class="pb-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">ID Transaksi</th>
                    <th class="pb-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Kasir</th>
                    <th class="pb-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Metode</th>
                    <th class="pb-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Total</th>
                    <th class="pb-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <template x-if="loading">
                    <template x-for="i in 5">
                        <tr class="animate-pulse">
                            <td class="py-4"><div class="h-4 w-24 bg-slate-100 rounded"></div></td>
                            <td class="py-4"><div class="h-4 w-20 bg-slate-100 rounded"></div></td>
                            <td class="py-4"><div class="h-4 w-16 bg-slate-100 rounded"></div></td>
                            <td class="py-4"><div class="h-4 w-20 bg-slate-200 rounded ml-auto"></div></td>
                            <td class="py-4"><div class="h-4 w-12 bg-slate-100 rounded ml-auto"></div></td>
                        </tr>
                    </template>
                </template>

                <template x-if="!loading && recentTransactions.length === 0">
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-400 italic">Belum ada transaksi</td>
                    </tr>
                </template>

                <template x-if="!loading">
                    <template x-for="trx in recentTransactions" :key="trx.id">
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 text-sm font-medium text-slate-900" x-text="trx.transaction_number"></td>
                            <td class="py-4 text-sm text-slate-600" x-text="trx.cashier"></td>
                            <td class="py-4">
                                <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider"
                                      :class="{
                                          'bg-blue-50 text-blue-600': trx.payment_method === 'cash',
                                          'bg-purple-50 text-purple-600': trx.payment_method !== 'cash'
                                      }"
                                      x-text="trx.payment_method"></span>
                            </td>
                            <td class="py-4 text-sm font-bold text-slate-900 text-right" x-text="formatRupiah(trx.total)"></td>
                            <td class="py-4 text-xs text-slate-400 text-right" x-text="trx.time"></td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>
    </div>
</div>
