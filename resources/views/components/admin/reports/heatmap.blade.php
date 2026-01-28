<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 print:border-none print:shadow-none avoid-page-break">
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
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-sm font-black text-slate-800 flex items-center gap-2 uppercase tracking-tight">
                <i data-lucide="activity" class="w-4 h-4 text-indigo-500"></i>
                Analisis Intensitas Transaksi (24 Jam)
            </h3>
            <p class="text-[11px] text-slate-400 font-medium tracking-wide">Pola keramaian berdasarkan hari dan jam sepanjang waktu</p>
            <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider mt-1" x-text="getActiveFiltersLabel()"></p>
        </div>
        <div class="hidden sm:block text-[10px] font-bold text-slate-400 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
            TOTAL: <span class="text-slate-800" x-text="heatmapData.total_transactions || 0"></span> DATA
        </div>
    </div>

    <!-- Heatmap Grid -->
    <div class="overflow-x-auto pb-4 scrollbar-hide">
        <div class="min-w-[850px] flex flex-col gap-1 heatmap-container">
            <!-- X-Axis Labels (Time) -->
            <div class="flex items-center mb-2">
                <div class="w-14 flex-shrink-0"></div> <!-- Spacer for Y-axis labels -->
                <div class="flex-1 flex justify-between heatmap-x-labels">
                    <template x-for="h in 24" :key="h">
                        <div class="w-full text-center border-l border-slate-50/50">
                            <span class="text-[8px] font-bold text-slate-400" x-text="(h-1).toString().padStart(2, '0') + ':00'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Heatmap Rows (7 Days) -->
            <template x-for="(dayName, dayIndex) in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="dayIndex">
                <div class="flex items-center group/row">
                    <!-- Y-Axis Label (Day) -->
                    <div class="w-14 flex-shrink-0 text-[10px] font-black text-slate-400 uppercase pr-4 text-right group-hover/row:text-indigo-500 transition-colors" x-text="dayName"></div>
                    
                    <!-- Hour Cells (24 hours: 0-23) -->
                    <div class="flex-1 flex gap-1 heatmap-cell-container">
                        <template x-for="h in 24" :key="h">
                            <div 
                                class="heatmap-cell flex-1 aspect-square rounded-[2px] border border-slate-100/30 cursor-pointer transition-all hover:ring-2 hover:ring-indigo-400 hover:scale-125 hover:z-10 relative group"
                                :class="getHeatmapColor(heatmapData.heatmap?.[dayIndex]?.[h-1] || 0, heatmapData.max_value || 1)"
                                @click="console.log('Day:', dayName, 'Hour:', (h-1) + ':00', 'Value:', heatmapData.heatmap?.[dayIndex]?.[h-1])"
                            >
                                <!-- Precise Tooltip on Hover -->
                                <div class="no-print absolute bottom-full left-1/2 -translate-x-1/2 mb-3 px-2 py-1.5 bg-slate-800 text-white text-[9px] font-bold rounded-lg shadow-2xl opacity-0 group-hover:opacity-100 whitespace-nowrap z-50 pointer-events-none transition-all scale-75 group-hover:scale-100 ring-1 ring-white/10">
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-slate-400 text-[8px] uppercase tracking-tighter" x-text="getDayName(dayIndex) + ' • ' + (h-1).toString().padStart(2, '0') + ':00'"></span>
                                        <span class="text-white text-[10px]" x-text="(heatmapData.heatmap?.[dayIndex]?.[h-1] || 0) + ' Transaksi'"></span>
                                    </div>
                                    <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Footer: Legend & Strategic Peak Info -->
    <div class="mt-8 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-6 border-t border-slate-50 pt-6">
        <!-- Legend (GitHub Contribution Style) -->
        <div class="flex items-center gap-4">
            <div class="flex flex-col gap-1">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Skala Intensitas</span>
                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400">
                    <span>SEPI</span>
                    <div class="flex gap-1">
                        <div class="heatmap-cell w-4 h-4 bg-slate-50 border border-slate-200 rounded-[2px]"></div>
                        <div class="heatmap-cell w-4 h-4 bg-green-100 border border-green-200/50 rounded-[2px]"></div>
                        <div class="heatmap-cell w-4 h-4 bg-green-300 border border-green-400/50 rounded-[2px]"></div>
                        <div class="heatmap-cell w-4 h-4 bg-green-500 border border-green-600/50 rounded-[2px]"></div>
                        <div class="heatmap-cell w-4 h-4 bg-green-700 border border-green-800/50 rounded-[2px]"></div>
                    </div>
                    <span>RAMAI</span>
                </div>
            </div>
        </div>
        
        <!-- Insight Box (Strategic Info) -->
        <template x-if="heatmapData.peak_hour !== null">
            <div class="flex-1 max-w-md flex items-center gap-4 py-3 px-5 bg-gradient-to-r from-slate-50 to-white rounded-2xl border border-slate-100 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 flex-shrink-0 animate-pulse">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Prime Time (Titik Puncak)</p>
                    <p class="text-xs font-black text-slate-800 truncate">
                        Sering terjadi pada jam <span class="text-indigo-600" x-text="heatmapData.peak_hour + ':00'"></span> di hari <span class="text-indigo-600 capitalize" x-text="getDayName(heatmapData.peak_day)"></span>
                    </p>
                </div>
                <div class="hidden sm:block text-right">
                    <div class="text-[10px] font-bold text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100 uppercase" x-text="heatmapData.max_value + ' trx'"></div>
                </div>
            </div>
        </template>
    </div>
</div>
