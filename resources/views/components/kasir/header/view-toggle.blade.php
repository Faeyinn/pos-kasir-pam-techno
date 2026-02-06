<div class="shrink-0" x-show="!isCartExpanded">
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
