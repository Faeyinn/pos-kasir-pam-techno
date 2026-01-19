<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Pola Transaksi per Jam</h3>
            <p class="text-sm text-slate-500">Perbandingan aktivitas transaksi setiap hari dalam seminggu</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-600">
            <span>Total: <strong x-text="hourlyData.total_transactions || 0"></strong> transaksi</span>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="relative" style="height: 300px;">
        <canvas id="hourlyPatternChart"></canvas>
    </div>

    <!-- Legend Info -->
    <div class="mt-4 text-xs text-slate-500 text-center">
        Periode: <span x-text="hourlyData.period?.start"></span> s/d <span x-text="hourlyData.period?.end"></span>
    </div>
</div>
