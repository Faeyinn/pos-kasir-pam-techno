{{-- Reusable Tag Selector Component --}}
@props([
    'selectedTags' => [],
    'modelName' => 'selectedTagIds'
])

<div 
    x-data="{
        searchQuery: '',
        focusedIndex: -1,
        allTags: availableTags,
        getSelectedIds: () => [],

        init() {
            this.$watch('filteredTags', () => this.$nextTick(() => lucide.createIcons()));
        },

        get filteredTags() {
            if (this.searchQuery === '') return [];
            const query = this.searchQuery.toLowerCase();
            const selected = this.getSelectedIds() || [];
            
            return this.allTags.filter(tag => 
                tag.name.toLowerCase().includes(query) && 
                !selected.includes(tag.id)
            );
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
        <label class="block text-sm font-medium text-slate-700">
            Tag Produk <span class="text-red-500">*</span>
        </label>
        
        <button 
            type="button" 
            @click="$dispatch('open-manage-tags')"
            class="flex items-center justify-center w-8 h-8 text-slate-500 hover:text-slate-700 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-slate-100 transition-all shadow-sm"
            title="Kelola Tag"
        >
            <i data-lucide="more-horizontal" class="w-5 h-5"></i>
        </button>
    </div>

    <div class="relative mb-3">
        <!-- Search Input -->
        <div class="relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input 
                type="text"
                x-model="searchQuery"
                @keydown.enter.prevent="selectFocused()"
                @keydown.arrow-down.prevent="focusedIndex = Math.min(focusedIndex + 1, filteredTags.length - 1)"
                @keydown.arrow-up.prevent="focusedIndex = Math.max(focusedIndex - 1, 0)"
                @keydown.escape="searchQuery = ''"
                class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                placeholder="Tambahkan tag untuk produk tersebut....."
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
            class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-auto"
            style="display: none;"
        >
            <ul class="py-1">
                <template x-for="(tag, index) in filteredTags" :key="tag.id">
                    <li>
                        <button
                            type="button"
                            @click="selectTag(tag.id)"
                            @mouseenter="focusedIndex = index"
                            class="w-full text-left px-4 py-2 text-sm flex items-center justify-between transition-colors"
                            :class="index === focusedIndex ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50'"
                        >
                            <span class="flex items-center gap-2">
                                <span 
                                    class="w-2 h-2 rounded-full"
                                    :style="`background-color: ${tag.color}`"
                                ></span>
                                <span x-text="tag.name"></span>
                            </span>
                            <i x-show="index === focusedIndex" data-lucide="corner-down-left" class="w-4 h-4 text-indigo-400"></i>
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        <!-- No Results -->
        <div 
            x-show="searchQuery.length > 0 && filteredTags.length === 0" 
            class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg p-4 text-center text-slate-500 text-sm"
            style="display: none;"
        >
            Tidak ada tag yang ditemukan
        </div>
    </div>
    
    <!-- Selected Tags List -->
    <div class="flex flex-wrap gap-2">
        <template x-for="tagId in {{ $modelName }}" :key="tagId">
            <span 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-white shadow-sm transition-all hover:opacity-90 active:scale-95 cursor-default"
                :style="`background-color: ${availableTags.find(t => t.id === tagId)?.color}`"
            >
                <span x-text="availableTags.find(t => t.id === tagId)?.name"></span>
                <button 
                    type="button"
                    x-on:click="$dispatch('toggle-tag', { path: '{{ $modelName }}', id: tagId })"
                    class="p-0.5 hover:bg-white/20 rounded transition-colors"
                >
                    <i data-lucide="x" class="w-3 h-3"></i>
                </button>
            </span>
        </template>
        
        <span x-show="{{ $modelName }}.length === 0" class="text-sm text-slate-400 italic py-1.5">
            Belum ada tag dipilih
        </span>
    </div>

    <p class="mt-2 text-xs text-slate-500">
        <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
        Ketik nama tag untuk mencari, lalu tekan Enter untuk memilih
    </p>
</div>
