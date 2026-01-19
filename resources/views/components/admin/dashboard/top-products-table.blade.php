@props(['loading' => false])

<div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Produk Terlaris</h3>
        </div>
        
        {{-- Period Dropdown Filter --}}
        <div class="relative" x-data="{ open: false }">
            <button 
                @click="open = !open"
                @click.outside="open = false"
                class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[44px]"
            >
                <span x-text="getPeriodLabel()"></span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
            </button>
            
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full mt-2 w-40 bg-white border border-slate-200 rounded-lg shadow-lg z-10 overflow-hidden"
                style="display: none;"
            >
                <button 
                    @click="changePeriod('daily'); open = false"
                    :class="period === 'daily' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50'"
                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    Hari Ini
                </button>
                <button 
                    @click="changePeriod('weekly'); open = false"
                    :class="period === 'weekly' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50'"
                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    7 Hari
                </button>
                <button 
                    @click="changePeriod('monthly'); open = false"
                    :class="period === 'monthly' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50'"
                    class="w-full text-left px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    30 Hari
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        {{-- Always show table structure --}}
        <table class="w-full">
            <thead>
                <tr class="bg-slate-100">
                    <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wide w-16">No</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wide">Produk</th>
                    <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wide w-28">Terjual</th>
                    <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 uppercase tracking-wide w-36">Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loading State --}}
                <template x-if="loading">
                    <tr>
                        <td colspan="4" class="py-4">
                            <div class="space-y-2">
                                <template x-for="i in 6" :key="i">
                                    <div class="flex gap-4 items-center py-2">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg animate-pulse"></div>
                                        <div class="flex-1 h-4 bg-slate-100 rounded animate-pulse"></div>
                                        <div class="w-16 h-4 bg-slate-100 rounded animate-pulse"></div>
                                        <div class="w-24 h-4 bg-slate-100 rounded animate-pulse"></div>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                </template>

                {{-- Empty State --}}
                <template x-if="!loading && topProducts.length === 0">
                    <tr>
                        <td colspan="4" class="py-16">
                            <div class="text-center">
                                <i data-lucide="package-x" class="w-16 h-16 text-slate-300 mx-auto mb-3"></i>
                                <p class="text-slate-500 font-medium">Belum ada data penjualan</p>
                                <p class="text-slate-400 text-sm mt-1">untuk periode ini</p>
                            </div>
                        </td>
                    </tr>
                </template>

                {{-- Table Data --}}
                <template x-if="!loading && topProducts.length > 0">
                    <template x-for="product in topProducts" :key="product.rank">
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="py-3.5 px-4">
                                <span 
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold"
                                    :class="{
                                        'bg-yellow-100 text-yellow-700': product.rank === 1,
                                        'bg-slate-200 text-slate-700': product.rank === 2,
                                        'bg-orange-100 text-orange-700': product.rank === 3,
                                        'bg-slate-50 text-slate-600': product.rank > 3
                                    }"
                                    x-text="product.rank"
                                ></span>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-medium text-slate-900 text-sm" x-text="product.product_name"></p>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <p class="font-semibold text-slate-700 text-sm" x-text="product.total_qty"></p>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <p class="font-bold text-slate-900 text-sm" x-text="formatRupiah(product.total_sales)"></p>
                            </td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>
    </div>
</div>
