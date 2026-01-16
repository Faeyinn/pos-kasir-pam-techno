<div 
    x-show="showPaymentModal" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="showPaymentModal = false"
    x-cloak
    x-data="{
        selectedPaymentMethod: 'tunai',
        amountReceived: '',
        get change() {
            const received = parseFloat(this.amountReceived) || 0;
            return received - cartTotal;
        },
        init() {
            this.$watch('showPaymentModal', (value) => {
                if (value) {
                    this.amountReceived = '';
                    this.selectedPaymentMethod = 'tunai';
                }
            });

            this.$watch('selectedPaymentMethod', (value) => {
                if (value !== 'tunai') {
                    this.amountReceived = cartTotal.toString();
                } else {
                    this.amountReceived = '';
                }
            });
        }
    }"
>
    <div 
        class="bg-white rounded-3xl max-w-4xl w-full shadow-2xl transform transition-all overflow-hidden"
        x-show="showPaymentModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        @click.stop
    >
        <!-- Header -->
        <div class="bg-gray-50 px-8 py-6 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="showPaymentModal = false" class="p-2 hover:bg-gray-200 rounded-xl transition-colors">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
                </button>
                <h2 class="text-2xl font-bold text-gray-900">Pembayaran</h2>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-xl border border-gray-200">
                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                    <span class="text-sm font-semibold text-gray-600">Kasir</span>
                </div>
                <!-- Top Right Close Button -->
                <button 
                    @click="showPaymentModal = false" 
                    class="p-2 hover:bg-red-50 hover:text-red-500 rounded-xl transition-colors group"
                    title="Tutup Popup"
                >
                    <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:text-red-500 transition-colors"></i>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Left: Ringkasan Belanja -->
                <div class="border border-gray-200 rounded-2xl p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Ringkasan Belanja</h3>
                    
                    <div class="space-y-3 mb-4 max-h-48 overflow-y-auto custom-scrollbar pr-2">
                        <template x-for="(item, index) in cart" :key="item.id">
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex-1">
                                    <span class="text-gray-600" x-text="(index + 1) + '. ' + item.name"></span>
                                </div>
                                <span class="font-semibold text-gray-900 ml-4" x-text="'Rp ' + formatNumber(getItemPrice(item) * item.qty)"></span>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-semibold text-gray-900" x-text="'Rp ' + formatNumber(cartTotal)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Diskon</span>
                            <span class="font-semibold text-gray-900">- Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                            <span class="font-bold text-gray-900">Total Bayar</span>
                            <span class="font-black text-xl text-blue-600" x-text="'Rp ' + formatNumber(cartTotal)"></span>
                        </div>
                    </div>
                </div>

                <!-- Right: Metode Pembayaran -->
                <div class="border border-gray-200 rounded-2xl p-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Metode Pembayaran</h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            @click="selectedPaymentMethod = 'tunai'"
                            :class="selectedPaymentMethod === 'tunai' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                            class="py-4 rounded-xl font-bold text-sm border-2 transition-all"
                        >
                            Tunai
                        </button>
                        <button 
                            @click="selectedPaymentMethod = 'kartu'"
                            :class="selectedPaymentMethod === 'kartu' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                            class="py-4 rounded-xl font-bold text-sm border-2 transition-all"
                        >
                            Kartu
                        </button>
                        <button 
                            @click="selectedPaymentMethod = 'qris'"
                            :class="selectedPaymentMethod === 'qris' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                            class="py-4 rounded-xl font-bold text-sm border-2 transition-all"
                        >
                            QRIS
                        </button>
                        <button 
                            @click="selectedPaymentMethod = 'ewallet'"
                            :class="selectedPaymentMethod === 'ewallet' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                            class="py-4 rounded-xl font-bold text-sm border-2 transition-all"
                        >
                            E-Wallet
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom: Jumlah yang Diterima -->
            <div class="border border-gray-200 rounded-2xl p-6">
                <div class="flex items-center gap-4 mb-4">
                    <h3 class="text-base font-bold text-gray-800">Jumlah yang Diterima</h3>
                    <button 
                        x-show="selectedPaymentMethod === 'tunai'"
                        @click="amountReceived = cartTotal.toString()"
                        class="px-4 py-1.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg font-bold text-[10px] uppercase tracking-wider hover:bg-blue-100 transition-all active:scale-95 whitespace-nowrap"
                    >
                        Uang Pas
                    </button>
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="flex-1">
                        <div class="relative flex items-center gap-3">
                            <div class="relative flex-1 group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">Rp</span>
                                <input 
                                    type="text" 
                                    x-model="amountReceived"
                                    @input="amountReceived = amountReceived.replace(/[^0-9]/g, '')"
                                    placeholder="0"
                                    class="w-full pl-12 pr-12 py-4 border-2 border-gray-200 rounded-xl text-lg font-bold text-gray-900 focus:outline-none focus:border-blue-500 transition-colors"
                                >
                                <!-- Clear Button -->
                                <button 
                                    x-show="amountReceived.length > 0"
                                    @click="amountReceived = ''; $nextTick(() => window.lucide && lucide.createIcons())"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 p-1.5 bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition-all active:scale-90"
                                    type="button"
                                >
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 text-sm">
                            <span class="text-gray-600">Kembalian: </span>
                            <span 
                                class="font-bold"
                                :class="change >= 0 ? 'text-green-600' : 'text-red-500'"
                                x-text="'Rp ' + formatNumber(Math.max(0, change))"
                            ></span>
                        </div>
                    </div>

                    <button
                        @click="confirmPayment()"
                        :disabled="(parseFloat(amountReceived) || 0) < cartTotal"
                        class="px-12 py-4 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-bold text-base shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed active:scale-95"
                    >
                        Konfirmasi Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
