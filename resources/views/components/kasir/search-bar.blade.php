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

        <div class="relative shrink-0">
             <button 
                @click="openScanner()" 
                class="h-full aspect-square flex items-center justify-center bg-white/50 backdrop-blur-sm border border-gray-200/50 rounded-2xl sm:rounded-[2rem] shadow-sm hover:bg-gray-50 transition-all active:scale-95 text-gray-500 hover:text-blue-600"
                title="Scan Barcode"
            >
                <i data-lucide="scan-line" class="w-6 h-6"></i>
            </button>
        </div>



        <div x-data="{ 
            open: false, 
            tagQuery: '',
            get filteredTagsList() {
                // Pastikan availableTags ada dan berupa array
                let tags = this.availableTags || [];
                
                // Helper helper untuk sort abjad
                const sortAlpha = (a, b) => {
                    const nameA = (a.name || '').toString().toLowerCase().trim();
                    const nameB = (b.name || '').toString().toLowerCase().trim();
                    return nameA.localeCompare(nameB);
                };

                // Jika query kosong, sort abjad biasa
                if (!this.tagQuery) {
                    return [...tags].sort(sortAlpha);
                }

                const query = this.tagQuery.toLowerCase().trim();
                
                // Filter & Sort
                return tags.filter(tag => (tag.name || '').toLowerCase().includes(query))
                    .sort((a, b) => {
                        const nameA = (a.name || '').toLowerCase().trim();
                        const nameB = (b.name || '').toLowerCase().trim();
                        
                        // Prioritas: Exact Match paling atas
                        const exactA = nameA === query;
                        const exactB = nameB === query;

                        if (exactA && !exactB) return -1;
                        if (!exactA && exactB) return 1;

                        // Sisanya urut abjad
                        return nameA.localeCompare(nameB);
                    });
            },
            get groupedTags() {
                const tags = this.filteredTagsList;
                const groups = {};
                
                tags.forEach(tag => {
                    const name = (tag.name || '').trim();
                    if (!name) return;
                    
                    let char = name.charAt(0).toUpperCase();
                    if (!/[A-Z]/.test(char)) char = '#';
                    
                    if (!groups[char]) {
                        groups[char] = [];
                    }
                    groups[char].push(tag);
                });

                return Object.keys(groups).sort().map(key => ({
                    letter: key,
                    tags: groups[key]
                }));
            }
        }" class="relative shrink-0">
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
                x-trap="open"
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
                    <input 
                        type="text" 
                        x-model="tagQuery"
                        placeholder="Cari tag..."
                        class="w-full px-3 py-2 mb-3 text-sm border-b focus:outline-none focus:border-blue-500"
                    >

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

                    <div class="max-h-[400px] overflow-y-auto custom-scrollbar flex flex-col gap-4">
                        <template x-for="group in groupedTags" :key="group.letter">
                            <div class="w-full">
                                <div x-text="group.letter" class="text-xs font-bold text-gray-400 mb-2 border-b border-gray-100 pb-1 sticky top-0 bg-white z-10"></div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="tag in group.tags" :key="tag.id">
                                        <button 
                                            @click="toggleTag(tag.id)"
                                            class="px-5 py-2.5 rounded-full text-sm font-medium transition-all border select-none active:scale-95 shadow-sm"
                                            :class="selectedTags.includes(tag.id) 
                                                ? 'text-white shadow-md' 
                                                : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300'"
                                            :style="selectedTags.includes(tag.id) 
                                                ? `background-color: ${tag.color}; border-color: ${tag.color}; box-shadow: 0 4px 6px -1px ${tag.color}40` 
                                                : ''"
                                        >
                                            <span x-text="tag.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div x-show="tagQuery !== '' && groupedTags.length === 0" class="w-full text-center py-2 text-sm text-gray-500">
                            Tag tidak ditemukan
                        </div>
                    </div>

                    <div x-show="selectedTags.length === 0" class="mt-4 text-center py-4 border-t border-dashed border-gray-100">
                        <p class="text-xs text-gray-400">Pilih tag untuk menyaring produk</p>
                    </div>
                </div>
            </div>
        </div>
        

    </div>

    <div class="flex gap-2 items-start justify-between min-h-[40px]">
        <div class="flex-1 flex gap-2 overflow-x-auto pb-2 scrollbar-hide -mx-2 px-2">

            <template x-if="selectedTags.length > 0">
                <div class="flex gap-2">
                    <template x-for="tagId in selectedTags" :key="'active-' + tagId">
                        <button 
                            @click="toggleTag(tagId)"
                            class="flex items-center gap-2 px-5 py-2.5 rounded-xl sm:rounded-full text-white shadow-lg transition-all font-bold text-xs sm:text-sm group animate-in fade-in zoom-in duration-200 active:scale-95 border hover:opacity-90"
                            :style="`background-color: ${availableTags.find(t => t.id === tagId)?.color}; border-color: ${availableTags.find(t => t.id === tagId)?.color}`"
                        >
                            <span x-text="availableTags.find(t => t.id === tagId)?.name"></span>
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

            <template x-if="selectedTags.length === 0">
                <div class="flex gap-2">
                    <template x-for="tag in popularTags" :key="'popular-' + tag.id">
                        <button 
                            @click="toggleTag(tag.id)"
                            class="px-5 py-2.5 rounded-xl sm:rounded-full font-bold text-xs sm:text-sm bg-white/50 backdrop-blur-sm border-2 border-gray-200/50 text-gray-400 hover:border-gray-200 hover:text-gray-600 transition-all active:scale-95 whitespace-nowrap"
                            x-text="tag.name"
                        ></button>
                    </template>
                </div>
            </template>
        </div>

        <div class="shrink-0">
            <button 
                @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'" 
                class="w-[44px] h-[44px] flex items-center justify-center bg-white/50 backdrop-blur-sm border border-gray-200/50 rounded-xl sm:rounded-full shadow-sm hover:bg-gray-50 transition-all active:scale-95 text-gray-500 hover:text-blue-600"
                :title="viewMode === 'grid' ? 'Tampilan List' : 'Tampilan Grid'"
            >
                <div x-show="viewMode === 'grid'">
                    <i data-lucide="list" class="w-5 h-5"></i>
                </div>
                <div x-show="viewMode === 'list'" style="display: none;">
                    <i data-lucide="layout-grid" class="w-5 h-5"></i>
                </div>
            </button>
        </div>
    </div>
</div>
