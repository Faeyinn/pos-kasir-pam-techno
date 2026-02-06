<div 
    class="fixed inset-0 z-40 bg-gray-900/10 lg:relative lg:bg-transparent lg:z-20 shrink-0 h-full transition-all duration-300 ease-in-out"
    :class="[
        mobileCartOpen ? 'flex justify-end' : 'hidden lg:block',
        isCartExpanded ? 'lg:w-[55%]' : 'lg:w-95'
    ]"
    @click.self="mobileCartOpen = false"
>
    <div 
        class="w-[85%] sm:w-80 md:w-full h-full flex flex-col bg-white overflow-hidden border-l border-gray-100"
        @click.stop
    >
        <div class="p-6 border-b border-gray-50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-bold text-gray-800">Keranjang</h3>
                <button 
                    @click="toggleCartExpansion()"
                    class="hidden lg:flex w-8 h-8 items-center justify-center rounded-lg hover:bg-gray-50 text-gray-400 hover:text-blue-600 transition-all active:scale-95"
                    :title="isCartExpanded ? 'Kecilkan Keranjang' : 'Perbesar Keranjang'"
                >
                    <i :data-lucide="isCartExpanded ? 'minimize-2' : 'maximize-2'" class="w-4 h-4"></i>
                </button>
            </div>
            <div 
                class="px-3 py-1.5 rounded-full text-[10px] font-bold tracking-tight uppercase"
                :class="paymentType === 'wholesale' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                x-text="paymentType === 'wholesale' ? 'Mode Grosir' : 'Mode Eceran'"
            ></div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
            <template x-if="cart.length === 0">
                <div class="flex flex-col items-center justify-center h-full text-gray-300 py-10">
                    <i data-lucide="shopping-cart" class="w-12 h-12 mb-4 opacity-20"></i>
                    <p class="text-sm font-medium">Keranjang masih kosong</p>
                </div>
            </template>
            <template x-for="item in cart" :key="item.id">
                <x-kasir.cart.item />
            </template>
        </div>

        <div class="shrink-0">
            <div class="p-6 bg-white space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-gray-800">Total</span>
                    <span class="text-xl font-black text-blue-600" x-text="'Rp ' + formatNumber(cartTotal)"></span>
                </div>

                <div class="flex items-center justify-between text-sm text-gray-500">
                    <span>Total item (pcs)</span>
                    <span class="font-bold text-gray-700" x-text="formatNumber(cartTotalQtyDasar)"></span>
                </div>

                <div class="space-y-3">
                    <button 
                        @click="showPaymentModal = true"
                        :disabled="cart.length === 0"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl font-bold text-base shadow-lg shadow-blue-200 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Proses Pembayaran (<span x-text="paymentType === 'wholesale' ? 'Grosir' : 'Eceran'"></span>)
                    </button>

                    <button 
                        @click="clearCart()"
                        :disabled="cart.length === 0"
                        class="w-full bg-white border-2 border-red-100 text-red-500 hover:bg-red-50 py-3 rounded-2xl font-bold text-sm transition-all active:scale-[0.98] disabled:opacity-30"
                    >
                        Batalkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
