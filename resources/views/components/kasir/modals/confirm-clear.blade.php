<div 
    x-show="showClearCartModal" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
    @click.self="showClearCartModal = false"
    x-cloak
>
    <div 
        class="bg-white rounded-[2rem] max-w-sm w-full shadow-2xl overflow-hidden transform transition-all"
        x-show="showClearCartModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="scale-95 translate-y-4 opacity-0"
        x-transition:enter-end="scale-100 translate-y-0 opacity-100"
    >
        <div class="p-8 text-center">

            <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="trash-2" class="w-10 h-10 text-red-500"></i>
            </div>

            <h3 class="text-xl font-bold text-gray-900 mb-2">Kosongkan Keranjang?</h3>
            <p class="text-sm text-gray-500 font-medium leading-relaxed px-4">
                Semua item yang telah Anda pilih akan dihapus dari daftar belanja.
            </p>
        </div>

        <div class="p-6 bg-gray-50/50 flex gap-3">
            <button 
                @click="showClearCartModal = false"
                class="flex-1 px-6 py-4 rounded-xl font-bold text-sm text-gray-600 hover:bg-gray-100 transition-all border border-gray-100"
            >
                Kembali
            </button>
            <button 
                @click="cart = []; showClearCartModal = false; $nextTick(() => lucide.createIcons())"
                class="flex-1 px-6 py-4 rounded-xl font-bold text-sm bg-red-500 hover:bg-red-600 text-white shadow-lg shadow-red-200 transition-all active:scale-95"
            >
                Ya, Hapus
            </button>
        </div>
    </div>
</div>
