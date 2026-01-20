@props(['loading' => false])

<div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-bold text-slate-900">Distribusi per label</h3>
        <p class="text-sm text-slate-500 mt-1">Distribusi Hari Ini</p>
    </div>
    <div class="relative" style="height: 300px;">
        <template x-if="loading">
            <div class="absolute inset-0 bg-slate-50 rounded-xl animate-pulse"></div>
        </template>
        <template x-if="!loading && categoryData.empty">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <i data-lucide="pie-chart" class="w-12 h-12 text-slate-300 mx-auto mb-2"></i>
                    <p class="text-sm text-slate-400">Belum ada data kategori</p>
                </div>
            </div>
        </template>
        <canvas id="categoryChart" x-show="!loading && !categoryData.empty"></canvas>
    </div>
</div>
