<div 
    x-on:click="addToCart(product)"
    class="bg-white rounded-2xl sm:rounded-3xl p-3 sm:p-5 border border-gray-100 shadow-sm hover:shadow-xl transition-all cursor-pointer group active:scale-95 flex flex-col justify-between h-full"
>
    <div>
        <!-- Product Photo Container -->
        <div class="aspect-square bg-gray-50 rounded-xl sm:rounded-2xl mb-3 sm:mb-4 overflow-hidden group-hover:bg-blue-50 transition-colors relative flex items-center justify-center">
            
            <!-- Icon Dus (Default / Fallback) -->
            <div 
                class="w-full h-full flex items-center justify-center bg-gray-50"
                x-show="!product.image"
            >
                <i data-lucide="package" class="w-8 h-8 sm:w-12 sm:h-12 text-gray-300 group-hover:text-blue-300 transition-colors"></i>
            </div>

            <!-- Real Product Image (Only if exists) -->
            <img 
                x-show="product.image"
                x-bind:src="product.image ? (product.image.startsWith('http') ? product.image : '/storage/' + product.image) : ''" 
                x-bind:alt="product.name"
                class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                x-on:error="product.image = null; $nextTick(() => lucide.createIcons())"
                style="display: none;"
            >
        </div>

        <h4 class="font-bold text-gray-800 text-xs sm:text-sm mb-1 line-clamp-2" x-text="product.name"></h4>
        <p class="text-[8px] sm:text-[10px] text-gray-400 uppercase font-black tracking-widest mb-2" x-text="product.category"></p>
    </div>
    <div>
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <div class="font-black text-blue-600 text-sm sm:text-lg" x-text="'Rp ' + formatNumber(paymentType === 'wholesale' ? product.wholesale : product.price)"></div>
                <div 
                    x-show="paymentType === 'wholesale'" 
                    class="text-[8px] sm:text-[10px] font-bold text-purple-600 mt-0.5"
                    x-text="'Per ' + product.wholesaleUnit + ' (' + product.wholesaleQtyPerUnit + ' pcs)'"
                ></div>
            </div>
            <div class="hidden sm:block bg-blue-600 text-white p-2 rounded-xl opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0 text-xs shadow-lg">
                <i data-lucide="plus" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="mt-1 sm:mt-2 text-[8px] sm:text-[10px] font-bold text-gray-400" x-text="'Stok: ' + product.stock + ' unit'"></div>
    </div>
</div>
