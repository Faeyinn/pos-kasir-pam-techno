<div class="bg-gray-50/80 rounded-2xl p-5 relative group border border-transparent hover:border-gray-200 transition-all">

    <button @click="removeFromCart(item.id)" class="absolute top-4 right-4 text-red-400 hover:text-red-600 transition-colors">
        <i data-lucide="x" class="w-4 h-4"></i>
    </button>

    <h5 class="font-bold text-gray-800 text-base mb-1 pr-6" x-text="item.name"></h5>
    <div class="flex items-center gap-2 mb-4">
        <div class="text-[10px] font-black text-blue-600 uppercase tracking-wider" x-text="isWholesale(item) ? 'GROSIR' : 'ECERAN'"></div>
        
        <!-- Discount Indicator (only show if has discount and not wholesale) -->
        <template x-if="item.hasDiscount && !isWholesale(item)">
            <div class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded font-black uppercase">
                DISKON
            </div>
        </template>
        
        <div class="text-[10px] font-semibold text-purple-600" x-text="'(' + (getSelectedUnit(item)?.name || '-') + ')'" ></div>
    </div>

    <template x-if="item.units && item.units.length > 1">
        <div class="mb-4">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Satuan</label>
            <select
                class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                :value="item.selectedUnitId"
                @change="setItemUnit(item.id, $event.target.value)"
            >
                <template x-for="u in item.units" :key="u.id">
                    <option :value="u.id" x-text="u.qtyPerUnit > 1 ? `${u.name} (@ ${u.qtyPerUnit})` : u.name"></option>
                </template>
            </select>
        </div>
    </template>

    <div class="flex items-end justify-between">
        <div class="flex items-center bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
            <button @click="updateQty(item.id, -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-800 transition-colors text-lg font-bold">-</button>
            <span class="w-10 text-center text-sm font-bold text-gray-800" x-text="item.qty"></span>
            <button @click="updateQty(item.id, 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-800 transition-colors text-lg font-bold">+</button>
        </div>
        <div class="text-right">
            <div class="text-[10px] text-gray-400 mb-0.5 flex items-center justify-end gap-1.5">
                <span class="font-bold" :class="isWholesale(item) ? 'text-blue-600' : ''">
                    @ Rp <span x-text="formatNumber(getItemPrice(item))"></span>
                </span>
            </div>
            <div class="text-lg font-black text-gray-900" x-text="'Rp ' + formatNumber(getItemPrice(item) * item.qty)"></div>
        </div>
    </div>
</div>
