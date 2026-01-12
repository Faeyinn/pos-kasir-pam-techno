<div class="bg-gray-50/80 rounded-2xl p-5 relative group border border-transparent hover:border-gray-200 transition-all">
    <!-- Delete Button -->
    <button @click="removeFromCart(item.id)" class="absolute top-4 right-4 text-red-400 hover:text-red-600 transition-colors">
        <i data-lucide="x" class="w-4 h-4"></i>
    </button>

    <h5 class="font-bold text-gray-800 text-base mb-1 pr-6" x-text="item.name"></h5>
    <div class="flex items-center gap-2 mb-4">
        <div class="text-[10px] font-black text-blue-600 uppercase tracking-wider" x-text="isWholesale(item) ? 'GROSIR' : 'ECERAN'"></div>
        <div 
            x-show="paymentType === 'wholesale'" 
            class="text-[10px] font-semibold text-purple-600"
            x-text="'(' + item.wholesaleUnit + ')'"
        ></div>
    </div>

    <div class="flex items-end justify-between">
        <div class="flex items-center bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
            <button @click="updateQty(item.id, -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-800 transition-colors text-lg font-bold">-</button>
            <span class="w-10 text-center text-sm font-bold text-gray-800" x-text="item.qty"></span>
            <button @click="updateQty(item.id, 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-800 transition-colors text-lg font-bold">+</button>
        </div>
        <div class="text-right">
            <div class="text-[10px] text-gray-400 mb-0.5">
                <span x-show="paymentType === 'retail'">@ Rp </span>
                <span x-show="paymentType === 'wholesale'">@ Rp </span>
                <span x-text="formatNumber(getItemPrice(item))"></span>
                <span x-show="paymentType === 'wholesale'" x-text="'/' + item.wholesaleUnit"></span>
            </div>
            <div class="text-lg font-black text-gray-900" x-text="'Rp ' + formatNumber(getItemPrice(item) * item.qty)"></div>
        </div>
    </div>
</div>
