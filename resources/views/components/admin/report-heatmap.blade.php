<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Pola Frekuensi Transaksi</h3>
            <p class="text-sm text-slate-500">Visualisasi transaksi berdasarkan hari dan jam</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-600">
            <span>Total: <strong x-text="heatmapData.total_transactions || 0"></strong> transaksi</span>
        </div>
    </div>

    <!-- Heatmap Grid -->
    <div class="overflow-x-auto" style="max-height: 400px; overflow-y: auto;">
        <div class="inline-block min-w-full">
            <!-- Day Labels -->
            <div class="flex mb-1 sticky top-0 bg-white z-10 pb-1">
                <div class="w-10 flex-shrink-0"></div> <!-- Spacer for hour labels -->
                <template x-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="day">
                    <div class="flex-1 min-w-[3px] text-center text-[8px] font-medium text-slate-600" x-text="day"></div>
                </template>
            </div>

            <!-- Heatmap Rows (Business hours: 8-22) -->
            <template x-for="hour in 15" :key="hour">
                <div class="flex mb-1">
                    <!-- Hour Label (add 7 to start from 08:00) -->
                    <div class="w-10 flex-shrink-0 text-right pr-2 text-[8px] text-slate-500">
                        <span x-text="(hour + 7).toString().padStart(2, '0') + ':00'"></span>
                    </div>
                    
                    <!-- Day Cells (7 days) -->
                    <template x-for="day in 7" :key="day">
                        <div 
                            class="flex-1 min-w-[3px] mx-2 rounded cursor-pointer transition-all hover:scale-[3] hover:ring-1 hover:ring-indigo-400 hover:z-10"
                            :class="getHeatmapColor(heatmapData.heatmap?.[day - 1]?.[hour + 7] || 0, heatmapData.max_value || 1)"
                            :title="getHeatmapTooltip(day - 1, hour + 7)"
                            style="aspect-ratio: 1;"
                        >
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-600">
            <span>Rendah</span>
            <div class="flex gap-1">
                <div class="w-4 h-4 bg-slate-50 border border-slate-200 rounded"></div>
                <div class="w-4 h-4 bg-green-100 rounded"></div>
                <div class="w-4 h-4 bg-green-300 rounded"></div>
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <div class="w-4 h-4 bg-green-700 rounded"></div>
            </div>
            <span>Tinggi</span>
        </div>
        
        <!-- Peak Info -->
        <div x-show="heatmapData.peak_hour !== null" class="text-xs text-slate-600">
            <span class="font-medium">Jam Tersibuk:</span>
            <span class="px-2 py-1 bg-green-100 text-green-700 rounded ml-1">
                <span x-text="getDayName(heatmapData.peak_day || 0)"></span>
                <span x-text="(heatmapData.peak_hour || 0).toString().padStart(2, '0') + ':00'"></span>
            </span>
        </div>
    </div>
</div>
