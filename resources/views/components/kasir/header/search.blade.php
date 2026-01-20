<div class="relative group flex-1" x-data="{ showSuggestions: false }">
    <i data-lucide="search" class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5 transition-colors group-focus-within:text-blue-500"></i>
    <input 
        type="text" 
        placeholder="Cari produk atau scan..."
        class="w-full pl-14 pr-4 py-4 sm:py-5 bg-white/50 backdrop-blur-sm border border-gray-200/50 rounded-2xl sm:rounded-[2rem] shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-semibold text-base sm:text-lg"
        x-model="searchQuery"
        @focus="showSuggestions = true"
        @input="showSuggestions = true"
        @click.outside="showSuggestions = false"
        @keydown.escape="showSuggestions = false"
    />
    
    <!-- Result Suggestions -->
    <div 
        x-show="showSuggestions && searchQuery.length > 0"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden"
        style="display: none;"
    >
        <ul class="max-h-[300px] overflow-y-auto custom-scrollbar">
            <template x-for="product in products.filter(p => p.name.toLowerCase().includes(searchQuery.toLowerCase())).slice(0, 5)" :key="product.id">
                <li>
                    <button 
                        @click="searchQuery = product.name; showSuggestions = false"
                        class="w-full text-left px-5 py-3 hover:bg-gray-50 flex items-center gap-3 transition-colors border-b border-gray-50 last:border-0"
                    >
                        <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden shrink-0 border border-gray-200">
                            <template x-if="product.image">
                                <img :src="'/storage/' + product.image" class="w-full h-full object-cover" alt="">
                            </template>
                            <template x-if="!product.image">
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                                    <i data-lucide="image" class="w-5 h-5"></i>
                                </div>
                            </template>
                        </div>
                        <div>
                            <div x-text="product.name" class="font-bold text-gray-800 text-sm"></div>
                            <div x-text="'Rp ' + (product.price).toLocaleString('id-ID')" class="text-xs text-blue-600 font-bold"></div>
                        </div>
                    </button>
                </li>
            </template>
            <li x-show="products.filter(p => p.name.toLowerCase().includes(searchQuery.toLowerCase())).length === 0" class="px-5 py-4 text-center text-sm text-gray-400 font-medium">
                Tidak ada produk ditemukan
            </li>
        </ul>
    </div>
</div>
