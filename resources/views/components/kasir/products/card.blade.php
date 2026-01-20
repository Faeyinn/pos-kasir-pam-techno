<div 
    x-on:click="addToCart(product)"
    class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all cursor-pointer group active:scale-95 flex flex-col justify-between h-full relative overflow-hidden"
>
    <!-- Discount Badge -->
    <template x-if="product.hasDiscount">
        <div class="absolute top-0 right-0 m-3 sm:m-4 z-10">
            <div class="bg-gradient-to-br from-red-500 to-red-600 text-white px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg shadow-lg font-black text-[10px] sm:text-xs flex items-center gap-1">
                <i data-lucide="tag" class="w-3 h-3"></i>
                <span x-text="product.discount.type === 'percentage' 
                    ? `-${product.discount.value}%` 
                    : `-Rp ${formatNumber(product.discount.value)}`">
                </span>
            </div>
        </div>
    </template>

    <div>

        <div class="aspect-square bg-gray-50 rounded-xl sm:rounded-2xl mb-4 sm:mb-5 overflow-hidden group-hover:bg-blue-50 transition-colors relative flex items-center justify-center">

            <div 
                class="w-full h-full flex items-center justify-center bg-gray-50"
                x-show="!product.image"
            >
                <i data-lucide="package" class="w-10 h-10 sm:w-16 sm:h-16 text-gray-300 group-hover:text-blue-300 transition-colors"></i>
            </div>

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

        <div class="flex flex-wrap gap-1.5 mb-3" x-show="product.tags && product.tags.length > 0">
            <template x-for="tag in (product.tags || [])" :key="tag.id">
                 <span 
                    class="px-2.5 py-1 rounded-md text-[10px] font-bold border" 
                    :style="`background-color: ${tag.color}15; color: ${tag.color}; border-color: ${tag.color}30`"
                    x-text="tag.name"
                ></span>
            </template>
        </div>

    </div>

    <div>
        <div class="flex items-end justify-between">
            <div class="flex flex-col w-full">

                <!-- Price Section with Discount Support -->
                <div class="flex flex-col gap-0.5">
                    <!-- Original Price (struck through if has discount) -->
                    <template x-if="product.hasDiscount">
                        <div class="text-xs sm:text-sm text-gray-400 line-through font-medium decoration-2" 
                             x-text="'Rp ' + formatNumber(product.originalPrice)">
                        </div>
                    </template>
                    
                    <!-- Current Price (discounted or regular) -->
                    <div class="font-black text-base sm:text-lg" 
                         :class="product.hasDiscount ? 'text-red-600' : 'text-gray-900'"
                         x-text="'Rp ' + formatNumber(product.hasDiscount ? product.discountedPrice : product.price)">
                    </div>
                </div>

                <template x-if="product.wholesale > 0">
                    <div class="mt-1.5 p-2 bg-blue-50 rounded-lg border border-blue-100">
                        <div class="text-[10px] sm:text-[11px] leading-tight text-blue-800">
                            <span class="font-bold">Grosir:</span> 
                            <span>min. 1 <span x-text="product.wholesaleUnit"></span> (<span x-text="product.wholesaleQtyPerUnit"></span> pcs)</span>
                            <span class="font-bold text-blue-600 block sm:inline sm:ml-1">@ Rp <span x-text="formatNumber(product.wholesalePricePerPiece)"></span></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="absolute bottom-4 right-4 bg-blue-600 text-white p-2.5 rounded-xl opacity-0 group-hover:opacity-100 transition-all transform translate-y-4 group-hover:translate-y-0 shadow-lg shadow-blue-200">
                <i data-lucide="plus" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="mt-2 text-[10px] sm:text-xs font-bold text-gray-300" x-text="'Stok: ' + product.stock + ' unit'"></div>
    </div>
</div>
