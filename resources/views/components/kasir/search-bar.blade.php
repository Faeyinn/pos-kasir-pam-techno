<div class="flex flex-col gap-4 shrink-0 sticky top-0 z-20 pb-2">
    <div class="flex gap-3 relative">
        <div class="relative group flex-1">
            <i data-lucide="search" class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5 transition-colors group-focus-within:text-blue-500"></i>
            <input 
                type="text" 
                placeholder="Cari produk atau scan..."
                class="w-full pl-14 pr-4 py-4 sm:py-5 bg-white/50 backdrop-blur-sm border border-gray-200/50 rounded-2xl sm:rounded-[2rem] shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-semibold text-base sm:text-lg"
                x-model="searchQuery"
            />
        </div>
        
        <!-- Scan Button -->
        <div class="relative shrink-0">
             <button 
                @click="openScanner()" 
                class="h-full aspect-square flex items-center justify-center bg-white/50 backdrop-blur-sm border border-gray-200/50 rounded-2xl sm:rounded-[2rem] shadow-sm hover:bg-gray-50 transition-all active:scale-95 text-gray-500 hover:text-blue-600"
                title="Scan Barcode"
            >
                <i data-lucide="scan-line" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- View Toggle Buttons -->
        <div class="flex bg-white/50 backdrop-blur-sm border border-gray-200/50 rounded-2xl sm:rounded-[2rem] p-1 shadow-sm h-[56px] sm:h-[64px]">
            <button 
                @click="viewMode = 'grid'; $nextTick(() => window.lucide && lucide.createIcons())" 
                class="h-full px-4 rounded-xl sm:rounded-[1.5rem] transition-all flex items-center justify-center"
                :class="viewMode === 'grid' ? 'bg-white shadow text-blue-600' : 'text-gray-400 hover:text-gray-600'"
                title="Grid View"
            >
                <i data-lucide="layout-grid" class="w-6 h-6"></i>
            </button>
            <button 
                @click="viewMode = 'list'; $nextTick(() => window.lucide && lucide.createIcons())" 
                class="h-full px-4 rounded-xl sm:rounded-[1.5rem] transition-all flex items-center justify-center"
                :class="viewMode === 'list' ? 'bg-white shadow text-blue-600' : 'text-gray-400 hover:text-gray-600'"
                title="List View"
            >
                <i data-lucide="list" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Filter Button & Dropdown -->
        <div x-data="{ open: false }" class="relative shrink-0">
            <button 
                @click="open = !open; $nextTick(() => window.lucide && lucide.createIcons())" 
                class="h-full aspect-square flex items-center justify-center bg-white/50 backdrop-blur-sm border border-gray-200/50 rounded-2xl sm:rounded-[2rem] shadow-sm hover:bg-gray-50 transition-all active:scale-95 relative"
                :class="selectedTags.length > 0 ? 'border-blue-500 bg-blue-50 text-blue-600' : 'text-gray-500'"
            >
                <i data-lucide="filter" class="w-6 h-6"></i>
                <div 
                    x-show="selectedTags.length > 0" 
                    class="absolute -top-1 -right-1 bg-blue-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-white"
                    x-text="selectedTags.length"
                ></div>
            </button>
            <div 
                x-show="open" 
                @click.outside="open = false" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="absolute right-0 top-full mt-2 w-screen max-w-lg bg-white border border-gray-100 rounded-xl shadow-xl z-50"
                style="display: none;"
            >
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-bold text-gray-400 uppercase tracking-wider">Filter Tag</span>
                        <button 
                            @click="resetTags()" 
                            x-show="selectedTags.length > 0"
                            class="text-sm font-bold text-red-500 hover:text-red-600 transition-colors"
                        >
                            Reset Filter
                        </button>
                    </div>
                    
                    <div class="flex flex-wrap gap-3 max-h-[400px] overflow-y-auto custom-scrollbar">
                        <template x-for="tag in uniqueTags" :key="tag">
                            <button 
                                @click="toggleTag(tag)"
                                class="px-5 py-2.5 rounded-full text-sm font-medium transition-all border select-none active:scale-95 shadow-sm"
                                :class="selectedTags.includes(tag) 
                                    ? 'bg-blue-600 border-blue-600 text-white shadow-md shadow-blue-200 hover:bg-blue-700' 
                                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300'"
                                x-text="tag"
                            >
                            </button>
                        </template>
                    </div>
                    
                    <div x-show="selectedTags.length === 0" class="mt-4 text-center py-4 border-t border-dashed border-gray-100">
                        <p class="text-xs text-gray-400">Pilih tag untuk menyaring produk</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tags Display Area -->
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide -mx-2 px-2 min-h-[40px]">
        <!-- Active Tags -->
        <template x-if="selectedTags.length > 0">
            <div class="flex gap-2">
                <template x-for="tag in selectedTags" :key="'active-' + tag">
                    <button 
                        @click="toggleTag(tag)"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl sm:rounded-full bg-blue-600 border-2 border-blue-600 text-white shadow-lg shadow-blue-200 hover:bg-red-500 hover:border-red-500 hover:shadow-red-200 transition-all font-bold text-xs sm:text-sm group animate-in fade-in zoom-in duration-200 active:scale-95"
                    >
                        <span x-text="tag"></span>
                        <i data-lucide="x" class="w-4 h-4 group-hover:rotate-90 transition-transform"></i>
                    </button>
                </template>
                <button 
                    @click="resetTags()"
                    class="px-5 py-2.5 rounded-xl sm:rounded-full font-bold text-xs sm:text-sm text-red-500 hover:text-red-600 bg-red-50 hover:bg-red-100 transition-colors active:scale-95 border-2 border-red-50 hover:border-red-100"
                >
                    Reset
                </button>
            </div>
        </template>

        <!-- Recommended / Popular Tags (Show when no active tags) -->
        <template x-if="selectedTags.length === 0">
            <div class="flex gap-2">
                <!-- Label removed to look cleaner like categories -->
                <template x-for="tag in popularTags" :key="'popular-' + tag">
                    <button 
                        @click="toggleTag(tag)"
                        class="px-5 py-2.5 rounded-xl sm:rounded-full font-bold text-xs sm:text-sm bg-white/50 backdrop-blur-sm border-2 border-gray-200/50 text-gray-400 hover:border-gray-200 hover:text-gray-600 transition-all active:scale-95 whitespace-nowrap"
                        x-text="tag"
                    ></button>
                </template>
            </div>
        </template>
    </div>
</div>
