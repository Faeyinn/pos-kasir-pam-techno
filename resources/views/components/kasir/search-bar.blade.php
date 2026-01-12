<div class="flex flex-col gap-4 shrink-0 bg-gray-50/80 backdrop-blur-md sticky top-0 z-20 pb-2">
    <div class="relative group">
        <i data-lucide="search" class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5 transition-colors group-focus-within:text-blue-500"></i>
        <input 
            type="text" 
            placeholder="Cari produk atau scan..."
            class="w-full pl-14 pr-4 py-4 sm:py-5 bg-white border border-gray-100 rounded-2xl sm:rounded-[2rem] shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-semibold text-base sm:text-lg"
            x-model="searchQuery"
        />
    </div>

    <!-- Category Filters -->
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide -mx-2 px-2">
        @php
            $categories = [
                ['id' => 'all', 'name' => 'Semua'],
                ['id' => 'minuman', 'name' => 'Minuman'],
                ['id' => 'makanan', 'name' => 'Makanan'],
                ['id' => 'sembako', 'name' => 'Sembako'],
                ['id' => 'kebutuhan', 'name' => 'Kebutuhan'],
            ];
        @endphp
        @foreach($categories as $cat)
            <button 
                @click="selectedCategory = '{{ $cat['id'] }}'"
                class="px-5 sm:px-8 py-2.5 sm:py-3.5 rounded-xl sm:rounded-full whitespace-nowrap font-bold transition-all text-xs sm:text-sm border-2"
                :class="selectedCategory === '{{ $cat['id'] }}' 
                    ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200' 
                    : 'bg-white border-gray-100 text-gray-400 hover:border-gray-200'"
            >
                {{ $cat['name'] }}
            </button>
        @endforeach
    </div>
</div>
