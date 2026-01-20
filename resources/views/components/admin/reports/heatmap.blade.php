<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 print:border-none print:shadow-none">
    <style>
        @media print {
            .heatmap-cell {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
        }
        .heatmap-cell {
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Pola Frekuensi Transaksi</h3>
            <p class="text-sm text-slate-500">Visualisasi transaksi berdasarkan hari dan jam</p>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-600">
            <span>Total: <strong x-text="heatmapData.total_transactions || 0"></strong> transaksi</span>
        </div>
    </div>

    <!-- Heatmap Grid -->
    <div class="overflow-x-auto pb-2">
        <div class="min-w-[500px] mx-auto">
            <!-- Day Labels -->
            <div class="flex mb-3">
                <div class="w-14 flex-shrink-0"></div> <!-- Spacer for hour labels -->
                <template x-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="day">
                    <div class="flex-1 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest" x-text="day"></div>
                </template>
            </div>

            <!-- Heatmap Rows (Business hours: 8-22) -->
            <div class="space-y-1">
                <template x-for="hour in 15" :key="hour">
                    <div class="flex items-center">
                        <!-- Hour Label -->
                        <div class="w-14 flex-shrink-0 text-right pr-4 text-[10px] font-bold text-slate-400">
                            <span x-text="(hour + 7).toString().padStart(2, '0') + ':00'"></span>
                        </div>
                        
                        <!-- Day Cells (7 days) -->
                        <div class="flex-1 flex gap-1">
                            <template x-for="day in 7" :key="day">
                                <div 
                                    class="heatmap-cell flex-1 aspect-square rounded-[2px] border border-slate-100 cursor-pointer transition-all hover:ring-2 hover:ring-indigo-400 hover:z-10 relative group"
                                    :class="getHeatmapColor(heatmapData.heatmap?.[day - 1]?.[hour + 7] || 0, heatmapData.max_value || 1)"
                                    :title="getHeatmapTooltip(day - 1, hour + 7)"
                                >
                                    <!-- Tooltip on Hover (hidden on print) -->
                                    <div class="no-print absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[9px] rounded-md shadow-xl opacity-0 group-hover:opacity-100 whitespace-nowrap z-20 pointer-events-none transition-opacity">
                                        <span class="font-bold" x-text="heatmapData.heatmap?.[day - 1]?.[hour + 7] || 0"></span> Transaksi
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4 border-t border-slate-50 pt-6">
        <div class="flex items-center gap-3 text-xs text-slate-500 font-semibold uppercase tracking-wider">
            <span>Rendah</span>
            <div class="flex gap-1.5">
                <div class="heatmap-cell w-5 h-5 bg-slate-50 border border-slate-200 rounded-sm"></div>
                <div class="heatmap-cell w-5 h-5 bg-green-100 border border-green-200/50 rounded-sm"></div>
                <div class="heatmap-cell w-5 h-5 bg-green-300 border border-green-400/50 rounded-sm"></div>
                <div class="heatmap-cell w-5 h-5 bg-green-500 border border-green-600/50 rounded-sm"></div>
                <div class="heatmap-cell w-5 h-5 bg-green-700 border border-green-800/50 rounded-sm"></div>
            </div>
            <span>Tinggi</span>
        </div>
        
        <!-- Peak Info -->
        <template x-if="heatmapData.peak_hour !== null">
            <div class="text-[11px] font-bold text-slate-600 flex items-center gap-2 bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-100">
                <i data-lucide="zap" class="w-4 h-4 text-amber-500 fill-amber-500"></i>
                <span>Waktu Teramai: <span class="capitalize" x-text="getDayName(heatmapData.peak_day)"></span>, <span class="text-indigo-600" x-text="heatmapData.peak_hour + ':00'"></span></span>
            </div>
        </template>
    </div>
</div>
