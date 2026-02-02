<div 
    id="receipt-modal"
    x-show="showReceiptModal" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-[60] p-4"
    x-cloak
>
    <div 
        class="bg-white rounded-2xl max-w-sm w-full shadow-2xl overflow-hidden transform transition-all"
        x-show="showReceiptModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
    >

        <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white p-6 text-center relative overflow-hidden print-hide">
            <div class="relative z-10">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-3 border-2 border-white/30">
                    <i data-lucide="check-circle" class="w-8 h-8 text-white"></i>
                </div>
                <h2 class="text-xl font-bold mb-1">Pembayaran Berhasil</h2>
                <p class="text-white/80 text-xs font-medium">Transaksi telah selesai</p>
            </div>
        </div>

        <div class="p-4 bg-gray-50 max-h-[60vh] overflow-y-auto custom-scrollbar">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <template x-if="receiptData">
                    <div class="space-y-4">

                        <div class="text-center pb-3 border-b border-gray-100">
                            <h3 class="text-base font-bold text-gray-900">PAM TECHNO</h3>
                            <p class="text-[10px] text-gray-500 mt-0.5">Sistem Kasir Digital</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">================================</p>
                        </div>

                        <div class="space-y-1">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-600">No. Transaksi</span>
                                <span class="font-bold text-gray-900" x-text="receiptData.transactionNumber"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-600">Tanggal</span>
                                <span class="text-gray-900" x-text="receiptData.date"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-600">Waktu</span>
                                <span class="text-gray-900" x-text="receiptData.time"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-600">Kasir</span>
                                <span class="text-gray-900" x-text="receiptData.cashier"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-600">Jenis</span>
                                <span 
                                    class="font-bold"
                                    :class="paymentType === 'wholesale' ? 'text-purple-700' : 'text-blue-700'"
                                    x-text="paymentType === 'wholesale' ? 'GROSIR' : 'RETAIL'"
                                ></span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-3">
                            <p class="text-[10px] text-gray-500 mb-2">================================</p>
                            <div class="space-y-4 max-h-60 overflow-y-auto custom-scrollbar pr-1">
                                <template x-for="(group, gIndex) in (receiptData ? receiptData.groupedItems : [])" :key="gIndex">
                                    <div class="border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                                        <div class="flex justify-between items-start gap-2 mb-1">
                                            <p class="font-bold text-xs text-gray-900 flex-1" x-text="group.name"></p>
                                            <span class="font-black text-xs text-gray-900 whitespace-nowrap" x-text="'Rp ' + formatNumber(group.totalPrice)"></span>
                                        </div>
                                        <div class="space-y-0.5 ml-2 border-l-2 border-gray-100 pl-2">
                                            <template x-for="(part, pIndex) in group.parts" :key="pIndex">
                                                <div class="flex items-center">
                                                    <p class="text-[10px] text-gray-400">
                                                        <span x-text="part.qty"></span> x 
                                                        <span x-text="part.unitName" class="font-medium text-gray-500"></span>
                                                        <span x-text="' (@' + formatNumber(part.finalPrice) + ')'" class="text-[9px] opacity-60"></span>
                                                    </p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-3 space-y-1">
                            <p class="text-[10px] text-gray-500 mb-2">================================</p>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-600">Total</span>
                                <span class="font-bold text-gray-900" x-text="'Rp ' + formatNumber(receiptData.total)"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-600">Bayar</span>
                                <span class="text-gray-900" x-text="receiptData.paymentMethod === 'tunai' ? 'Tunai' : receiptData.paymentMethod === 'kartu' ? 'Kartu' : receiptData.paymentMethod === 'qris' ? 'QRIS' : 'E-Wallet'"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-600">Diterima</span>
                                <span class="text-gray-900" x-text="'Rp ' + formatNumber(receiptData.amountReceived)"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs pb-2">
                                <span class="text-gray-600">Kembalian</span>
                                <span class="font-bold text-gray-900" x-text="'Rp ' + formatNumber(receiptData.change)"></span>
                            </div>
                            <p class="text-[10px] text-gray-500 my-2">================================</p>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-sm font-bold text-gray-900">GRAND TOTAL</span>
                                <span class="text-base font-black text-gray-900" x-text="'Rp ' + formatNumber(receiptData.total)"></span>
                            </div>
                        </div>

                        <div class="text-center pt-3 border-t border-gray-100">
                            <p class="text-[10px] text-gray-500">================================</p>
                            <p class="text-[10px] text-gray-500 font-medium mt-1">Terima kasih atas kunjungan Anda</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">Barang yang sudah dibeli</p>
                            <p class="text-[10px] text-gray-500">tidak dapat dikembalikan</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="p-4 bg-white border-t border-gray-100 space-y-2 print-hide">
            <button
                @click="printReceipt()"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-lg shadow-blue-200 active:scale-95"
            >
                <i data-lucide="printer" class="w-4 h-4"></i>
                Cetak Struk
            </button>
            <button
                @click="finishTransaction()"
                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-xl font-semibold text-xs transition-all"
            >
                Selesai
            </button>
        </div>
    </div>
</div>
