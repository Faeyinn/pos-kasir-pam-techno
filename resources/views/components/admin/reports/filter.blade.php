<div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6">
    {{-- Filter Row --}}
    <div class="flex items-center justify-between gap-4">
        {{-- Left Group: Filters --}}
        <div class="flex items-center gap-3 flex-1 flex-wrap">
            {{-- Date Range --}}
            <div class="flex items-center gap-2">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </span>
                    <input 
                        type="text" 
                        x-ref="startDate"
                        x-init="
                            const fp = flatpickr($refs.startDate, { 
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd/m/Y',
                                allowInput: true,
                                onChange: (selectedDates, dateStr) => { filters.start_date = dateStr }
                            });
                            $watch('filters.start_date', value => fp.setDate(value));
                        "
                        :value="filters.start_date"
                        class="pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 w-32 bg-white"
                        placeholder="dd/mm/yyyy"
                    >
                </div>
                <span class="text-slate-400">-</span>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </span>
                    <input 
                        type="text" 
                        x-ref="endDate"
                        x-init="
                            const fp = flatpickr($refs.endDate, { 
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd/m/Y',
                                allowInput: true,
                                onChange: (selectedDates, dateStr) => { filters.end_date = dateStr }
                            });
                            $watch('filters.end_date', value => fp.setDate(value));
                        "
                        :value="filters.end_date"
                        class="pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 w-32 bg-white"
                        placeholder="dd/mm/yyyy"
                    >
                </div>
            </div>

            {{-- Payment Method Filter --}}
            {{-- Transaction Type Filter --}}
            <select 
                x-model="filters.transaction_type"
                class="px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 bg-white min-w-[150px]"
            >
                <option value="all">Semua Tipe</option>
                <option value="eceran">Eceran (Retail)</option>
                <option value="grosir">Grosir (Wholesale)</option>
            </select>

            {{-- Tags Filter --}}
            <div x-data="{ open: false }" class="relative">
                <button 
                    @click="open = !open" 
                    @click.outside="open = false"
                    class="px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-700 bg-white flex items-center gap-2 hover:bg-slate-50 w-48 justify-between"
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

        {{-- Right Group: Apply Button --}}
        <button 
            @click="applyFilters"
            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm hover:shadow-indigo-200 active:scale-95 flex items-center gap-2 flex-shrink-0"
        >
            <i data-lucide="filter" class="w-4 h-4"></i>
            Terapkan
        </button>
    </div>
</div>
