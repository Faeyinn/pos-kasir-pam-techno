<div class="bg-gray-50/80 rounded-2xl p-5 relative group border border-transparent hover:border-gray-200 transition-all">
    <button @click="removeFromCart(item.id)" class="absolute top-4 right-4 text-red-400 hover:text-red-600 transition-colors">
        <i data-lucide="x" class="w-4 h-4"></i>
    </button>

    <h5 class="font-bold text-gray-800 text-base mb-1 pr-6" x-text="item.name"></h5>
    
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <div class="text-[10px] font-black text-blue-600 uppercase tracking-wider" x-text="isWholesale(item) ? 'GROSIR' : 'ECERAN'"></div>
        
        <template x-if="item.hasDiscount && !isWholesale(item)">
            <div class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded font-black uppercase">
                DISKON
            </div>
        </template>

        <div class="w-full mt-1 text-[11px] font-bold text-gray-500 italic">
            <span x-text="'(' + formatNumber(getItemRequiredStock(item)) + ' pieces) '"></span>
            <template x-for="(s, index) in item.selections" :key="index">
                <span>
                    <span x-text="s.qty + ' ' + (getSelectedUnitForSelection(item, index)?.name || '')"></span>
                    <span x-if="index < item.selections.length - 1">, </span>
                </span>
            </template>
        </div>
    </div>

    <div class="space-y-4 mb-4">
        <template x-for="(s, index) in item.selections" :key="index">
            <div class="grid grid-cols-12 gap-2 lg:gap-3 items-end" :class="isCartExpanded ? 'lg:items-center lg:bg-white lg:p-2 lg:rounded-xl lg:border lg:border-gray-100/50' : ''">
                <div class="col-span-6" :class="isCartExpanded ? 'lg:col-span-6' : ''">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Satuan</label>
                    <select
                        class="w-full bg-white border border-gray-200 rounded-xl px-2 py-2 text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        :value="s.unitId"
                        @change="setItemUnit(item.id, $event.target.value, index)"
                    >
                        <template x-for="u in item.units" :key="u.id">
                            <option
                                :value="u.id"
                                x-text="u.qtyPerUnit > 1 ? `${u.name} (${u.qtyPerUnit})` : `${u.name} [eceran]`"
                            ></option>
                        </template>
                    </select>
                </div>
                
                <div class="col-span-5" :class="isCartExpanded ? 'lg:col-span-4' : ''">
                    <label x-show="isCartExpanded" class="hidden lg:block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 text-center">Jumlah</label>
                    <div class="flex items-center bg-white border border-gray-200 rounded-xl p-1 shadow-sm h-[34px] lg:h-[38px]">
                        <button @click="updateQty(item.id, -1, index)" class="w-8 h-full flex items-center justify-center text-gray-400 hover:text-gray-800 transition-colors text-base font-bold">-</button>
                        <span class="flex-1 text-center text-xs font-black text-gray-800" x-text="s.qty"></span>
                        <button @click="updateQty(item.id, 1, index)" class="w-8 h-full flex items-center justify-center text-gray-400 hover:text-gray-800 transition-colors text-base font-bold">+</button>
                    </div>
                </div>

                <div class="col-span-1 flex justify-center lg:justify-end pb-2 lg:pb-0" :class="isCartExpanded ? 'lg:col-span-2 lg:pr-2' : ''">
                    <button 
                        x-show="item.selections.length > 1"
                        @click="removeUnitSelection(item.id, index)" 
                        class="text-gray-300 hover:text-red-500 transition-colors p-1"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5 lg:w-4 lg:h-4"></i>
                    </button>
                    <div x-show="item.selections.length === 1" class="w-3.5 h-3.5 lg:w-4 lg:h-4"></div>
                </div>
            </div>
        </template>

        <button 
            x-show="item.units.length > item.selections.length"
            @click="addUnitSelection(item.id)"
            class="w-full py-2 border-2 border-dashed border-gray-100 rounded-xl text-[10px] font-black text-gray-400 hover:border-blue-100 hover:text-blue-500 hover:bg-blue-50/50 transition-all flex items-center justify-center gap-1.5"
        >
            <i data-lucide="plus" class="w-3 h-3"></i>
            TAMBAH SATUAN
        </button>
    </div>

    <div class="flex items-center justify-between pt-2 border-t border-gray-50">
        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Total Harga</div>
        <div class="text-right">
            <div class="text-lg font-black text-blue-600" x-text="'Rp ' + formatNumber(getItemTotalPrice(item))"></div>
        </div>
    </div>
</div>
