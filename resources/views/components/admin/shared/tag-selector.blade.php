{{-- Reusable Tag Selector Component --}}
@props([
    'selectedTags' => [],
    'modelName' => 'selectedTagIds'
])

<div 
    x-data="{
        searchQuery: '',
        focusedIndex: -1,
        allTags: window.__TAGS_DATA__ || [],
        getSelectedIds: () => [],

        init() {
            this.$watch('filteredTags', () => this.$nextTick(() => lucide.createIcons()));
            
            // Sync with global tags update
            document.addEventListener('tags-updated', (e) => {
                if (Array.isArray(e.detail)) {
                    this.allTags = e.detail;
                }
            });
        },

        get filteredTags() {
            if (this.searchQuery === '') return [];
            const query = this.searchQuery.toLowerCase();
            const selected = this.getSelectedIds() || [];
            
            // Filter tags, ensure valid ID, and remove duplicates
            const seenIds = new Set();
            return (this.allTags || [])
                .filter(tag => {
                    if (!tag || tag.id == null) return false;
                    if (seenIds.has(tag.id)) return false;
                    seenIds.add(tag.id);
                    return tag.name.toLowerCase().includes(query) && !selected.includes(tag.id);
                });
        },

        selectTag(tagId) {
            $dispatch('toggle-tag', { path: '{{ $modelName }}', id: tagId });
            this.searchQuery = '';
            this.focusedIndex = -1;
            // Force re-evaluation of filteredTags might happen automatically if getSelectedIds returns reference that changes?
            // Actually, if array is mutated, Alpine might not react if deep watch isn't on.
            // But we clear search query so list closes anyway.
        },

        selectFocused() {
            if (this.filteredTags.length > 0) {
                if (this.focusedIndex >= 0 && this.filteredTags[this.focusedIndex]) {
                    this.selectTag(this.filteredTags[this.focusedIndex].id);
                } else {
                    this.selectTag(this.filteredTags[0].id);
                }
            }
        }
    }"
    x-init="getSelectedIds = () => {{ $modelName }}"
>
    <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-bold text-slate-700">
            Kategori <span class="text-red-500">*</span>
        </label>
        
        <button 
            type="button" 
            @click="$dispatch('open-manage-tags')"
            class="flex items-center justify-center w-7 h-7 text-slate-400 hover:text-indigo-600 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-all shadow-sm active:scale-95"
            title="Kelola Tag"
        >
            <i data-lucide="settings-2" class="w-4 h-4"></i>
        </button>
    </div>

    <div class="relative mb-3">
        <!-- Search Input -->
        <div class="relative group">
            <i data-lucide="hash" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
            <input 
                type="text"
                x-model="searchQuery"
                @keydown.enter.prevent="selectFocused()"
                @keydown.arrow-down.prevent="focusedIndex = Math.min(focusedIndex + 1, filteredTags.length - 1)"
                @keydown.arrow-up.prevent="focusedIndex = Math.max(focusedIndex - 1, 0)"
                @keydown.escape="searchQuery = ''"
                class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all text-sm font-medium"
                placeholder="Cari kategori..."
            >
        </div>

        <!-- Dropdown Results -->
        <div 
            x-show="searchQuery.length > 0 && filteredTags.length > 0" 
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-60 overflow-hidden"
            style="display: none;"
        >
            <div class="p-1 max-h-60 overflow-y-auto custom-scrollbar">
                <template x-for="(tag, index) in filteredTags" :key="tag.id">
                    <button
                        type="button"
                        @click="selectTag(tag.id)"
                        @mouseenter="focusedIndex = index"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between transition-all"
                        :class="index === focusedIndex ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100' : 'text-slate-700 hover:bg-slate-50'"
                    >
                        <span class="flex items-center gap-2">
                            <span 
                                class="w-2.5 h-2.5 rounded-full border border-white/20"
                                :style="`background-color: ${tag.color}`"
                            ></span>
                            <span x-text="tag.name" class="font-bold"></span>
                        </span>
                        <i x-show="index === focusedIndex" data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                    </button>
                </template>
            </div>
        </div>

        <!-- No Results -->
        <div 
            x-show="searchQuery.length > 0 && filteredTags.length === 0" 
            class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl p-6 text-center"
            style="display: none;"
        >
            <div class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-2">
                <i data-lucide="search-x" class="w-5 h-5 text-slate-300"></i>
            </div>
            <p class="text-xs font-bold text-slate-400">Kategori tidak ditemukan</p>
        </div>
    </div>
    
    <!-- Selected Tags List -->
    <div class="flex flex-wrap gap-1.5 min-h-[32px]">
        <template x-for="tagId in {{ $modelName }}" :key="tagId">
            <span 
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[10px] font-black text-white shadow-sm transition-all hover:scale-105 active:scale-95 cursor-default relative group"
                :style="`background-color: ${allTags.find(t => t.id === tagId)?.color}`"
            >
                <span x-text="allTags.find(t => t.id === tagId)?.name" class="uppercase tracking-wider"></span>
                <button 
                    type="button"
                    x-on:click="$dispatch('toggle-tag', { path: '{{ $modelName }}', id: tagId })"
                    class="w-4 h-4 rounded-full bg-black/10 hover:bg-black/20 flex items-center justify-center transition-colors"
                >
                    <i data-lucide="x" class="w-2.5 h-2.5"></i>
                </button>
            </span>
        </template>
        
        <div x-show="{{ $modelName }}.length === 0" class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-xl border border-dashed border-slate-200 w-full">
            <div class="w-2 h-2 rounded-full bg-slate-200 animate-pulse"></div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pilih Kategori</span>
        </div>
    </div>

    <p class="mt-3 text-[10px] text-slate-400 flex items-center gap-1.5 italic">
        <i data-lucide="mouse-pointer-2" class="w-3 h-3"></i>
        <span>Klik untuk menghapus kategori yang dipilih</span>
    </p>
</div>
