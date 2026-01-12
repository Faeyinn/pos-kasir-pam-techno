<div 
    x-on:click="addToCart(product)"
    class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all cursor-pointer group active:scale-95 flex flex-col justify-between h-full relative overflow-hidden"
>
    <div>
        <!-- Product Photo Container -->
        <div class="aspect-square bg-gray-50 rounded-xl sm:rounded-2xl mb-4 sm:mb-5 overflow-hidden group-hover:bg-blue-50 transition-colors relative flex items-center justify-center">
            
            <!-- Icon Dus (Default / Fallback) -->
            <div 
                class="w-full h-full flex items-center justify-center bg-gray-50"
                x-show="!product.image"
            >
                <i data-lucide="package" class="w-10 h-10 sm:w-16 sm:h-16 text-gray-300 group-hover:text-blue-300 transition-colors"></i>
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

        <h4 class="font-bold text-gray-900 text-sm sm:text-base mb-2 line-clamp-2 leading-tight" x-text="product.name"></h4>
        
        <!-- Tags -->
        <div class="flex flex-wrap gap-1.5 mb-3" x-show="product.tags && product.tags.length > 0">
            <template x-for="tag in (product.tags || [])">
                 <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500 uppercase tracking-wide" x-text="tag"></span>
            </template>
        </div>

        <p class="text-[10px] sm:text-xs text-gray-400 uppercase font-black tracking-widest mb-3" x-text="product.category"></p>
    </div>
    
    <div>
        <div class="flex items-end justify-between">
            <div class="flex flex-col">
                <div class="font-black text-blue-600 text-base sm:text-xl" x-text="'Rp ' + formatNumber(paymentType === 'wholesale' ? product.wholesale : product.price)"></div>
                <div 
                    x-show="paymentType === 'wholesale'" 
                    class="text-[10px] sm:text-xs font-bold text-purple-600 mt-1"
                    x-text="'Per ' + product.wholesaleUnit + ' (' + product.wholesaleQtyPerUnit + ' pcs)'"
                ></div>
            </div>
            <div class="bg-blue-600 text-white p-2.5 rounded-xl opacity-0 group-hover:opacity-100 transition-all transform translate-y-4 group-hover:translate-y-0 shadow-lg shadow-blue-200">
                <i data-lucide="plus" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="mt-2 text-[10px] sm:text-xs font-bold text-gray-300" x-text="'Stok: ' + product.stock + ' unit'"></div>
    </div>
</div>
