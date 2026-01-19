<div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6">
    {{-- Filter Row --}}
    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-end justify-between">
        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto flex-wrap">
            {{-- Date Range --}}
            <div class="flex gap-2 items-center">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </span>
                    <input 
                        type="date" 
                        x-model="filters.start_date"
                        class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 w-full sm:w-auto"
                    >
                </div>
                <span class="text-slate-400">-</span>
                <div class="relative">
                    <input 
                        type="date" 
                        x-model="filters.end_date"
                        class="px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 w-full sm:w-auto"
                    >
                </div>
            </div>

            {{-- Type Filter --}}
            <select 
                x-model="filters.payment_type"
                class="px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 bg-white w-full sm:w-auto"
            >
                <option value="all">Semua Tipe</option>
                <option value="retail">Eceran</option>
                <option value="wholesale">Grosir</option>
            </select>

            {{-- Tags Filter (Multi-select simulation) --}}
            <div x-data="{ open: false }" class="relative w-full sm:w-auto">
                <button 
                    @click="open = !open" 
                    @click.outside="open = false"
                    class="px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white flex items-center gap-2 hover:bg-slate-50 w-full sm:w-48 justify-between"
                >
                    <span class="truncate" x-text="selectedTagsLabel"></span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                </button>

                <div 
                    x-show="open" 
                    class="absolute top-full left-0 mt-1 w-64 bg-white border border-slate-200 rounded-lg shadow-lg z-20 p-2 max-h-60 overflow-y-auto"
                    style="display: none;"
                >
                    <template x-for="tag in availableTags" :key="tag.id">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 rounded cursor-pointer">
                            <input 
                                type="checkbox" 
                                :value="tag.id" 
                                x-model="filters.tags"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-sm text-slate-700" x-text="tag.name"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        {{-- Apply Button --}}
        <button 
            @click="applyFilters"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2 w-full sm:w-auto"
        >
            <i data-lucide="filter" class="w-4 h-4"></i>
            Terapkan
        </button>
    </div>
</div>
