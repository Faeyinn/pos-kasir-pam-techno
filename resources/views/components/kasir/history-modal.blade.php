<div 
    x-show="showHistoryModal" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="showHistoryModal = false"
    x-cloak
>
    <div 
        class="bg-white rounded-2xl max-w-4xl w-full shadow-2xl overflow-hidden transform transition-all max-h-[90vh] flex flex-col"
        x-show="showHistoryModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
    >
        <!-- Header -->
        <div class="bg-gray-50 px-6 py-5 border-b border-gray-200 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i data-lucide="history" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Riwayat Transaksi</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar transaksi hari ini</p>
                </div>
            </div>
            <button @click="showHistoryModal = false" class="p-2 hover:bg-gray-200 rounded-xl transition-colors">
                <i data-lucide="x" class="w-5 h-5 text-gray-600"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <template x-if="transactionHistory.length === 0">
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <i data-lucide="inbox" class="w-16 h-16 mb-4 opacity-20"></i>
                    <p class="text-sm font-medium">Belum ada transaksi hari ini</p>
                </div>
            </template>

            <div class="space-y-3">
                <template x-for="(transaction, index) in transactionHistory" :key="transaction.transactionNumber">
                    <div class="bg-white border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer"
                         @click="viewTransactionDetail(transaction)">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-bold text-gray-900" x-text="transaction.transactionNumber"></span>
                                    <span 
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                        :class="transaction.paymentType === 'wholesale' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                        x-text="transaction.paymentType === 'wholesale' ? 'GROSIR' : 'RETAIL'"
                                    ></span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="text-gray-500">
                                        <i data-lucide="calendar" class="w-3 h-3 inline mr-1"></i>
                                        <span x-text="transaction.date + ' ' + transaction.time"></span>
                                    </div>
                                    <div class="text-gray-500">
                                        <i data-lucide="user" class="w-3 h-3 inline mr-1"></i>
                                        <span x-text="transaction.cashier"></span>
                                    </div>
                                    <div class="text-gray-500">
                                        <i data-lucide="credit-card" class="w-3 h-3 inline mr-1"></i>
                                        <span class="capitalize" x-text="transaction.paymentMethod === 'tunai' ? 'Tunai' : transaction.paymentMethod === 'kartu' ? 'Kartu' : transaction.paymentMethod === 'qris' ? 'QRIS' : 'E-Wallet'"></span>
                                    </div>
                                    <div class="text-gray-500">
                                        <i data-lucide="package" class="w-3 h-3 inline mr-1"></i>
                                        <span x-text="transaction.items.length + ' item'"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-black text-blue-600" x-text="'Rp ' + formatNumber(transaction.total)"></div>
                                <button 
                                    @click.stop="reprintReceipt(transaction)"
                                    class="mt-2 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1"
                                >
                                    <i data-lucide="printer" class="w-3 h-3"></i>
                                    Cetak Ulang
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between shrink-0">
            <div class="text-sm text-gray-600">
                Total: <span class="font-bold" x-text="transactionHistory.length + ' transaksi'"></span>
            </div>
            <button 
                @click="showHistoryModal = false"
                class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold text-sm transition-all"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
