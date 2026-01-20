<div x-data="{ 
    open: false, 
    tagQuery: '',
    get filteredTagsList() {
        let tags = this.availableTags || [];
        
        const sortAlpha = (a, b) => {
            const nameA = (a.name || '').toString().toLowerCase().trim();
            const nameB = (b.name || '').toString().toLowerCase().trim();
            return nameA.localeCompare(nameB);
        };

        if (!this.tagQuery) {
            return [...tags].sort(sortAlpha);
        }

        const query = this.tagQuery.toLowerCase().trim();
        
        return tags.filter(tag => (tag.name || '').toLowerCase().includes(query))
            .sort((a, b) => {
                const nameA = (a.name || '').toLowerCase().trim();
                const nameB = (b.name || '').toLowerCase().trim();
                
                const exactA = nameA === query;
                const exactB = nameB === query;

                if (exactA && !exactB) return -1;
                if (!exactA && exactB) return 1;

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
}" class="relative shrink-0" @click.outside="open = false">
    <button 
        x-ref="button"
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
