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
