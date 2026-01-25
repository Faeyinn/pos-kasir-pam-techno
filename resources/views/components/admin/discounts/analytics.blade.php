<div class="space-y-6">
    <div class="mb-4">
        <div class="flex items-center gap-3">
            <div class="w-2 h-8 bg-indigo-500 rounded-full"></div>
            <div>
                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">
                    Analisis Efektivitas Diskon
                </h2>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                    Data Performa 30 Hari Terakhir • Real-time
                </p>
                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider mt-1" x-text="getDateRangeLabel()"></p>
            </div>
        </div>
    </div>

    <x-admin.discounts.analytics-comparison />
    <x-admin.discounts.analytics-table />
</div>
