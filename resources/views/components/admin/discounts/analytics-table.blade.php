<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200">
        <h3 class="text-lg font-semibold text-slate-900">
            Efektivitas Setiap Diskon
        </h3>
        <p class="text-sm text-slate-600 mt-1">
            ROI = Revenue ÷ Diskon Diberikan × 100%
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase">
                        Nama Diskon
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase">
                        Dipakai
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase">
                        Diskon Diberikan
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase">
                        Revenue
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase">
                        Profit
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-700 uppercase">
                        ROI
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="discount in performance" :key="discount.id">
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900" x-text="discount.name"></div>
                            <div class="text-xs text-slate-500" 
                                 x-text="discount.type === 'percentage' ? discount.value + '%' : 'Rp ' + formatNumber(discount.value)">
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-slate-600"
                            x-text="discount.usage_count + 'x'">
                        </td>
                        <td class="px-6 py-4 text-right text-slate-600"
                            x-text="'Rp ' + formatNumber(discount.total_discount_given)">
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-slate-900"
                            x-text="'Rp ' + formatNumber(discount.total_revenue)">
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-slate-900"
                            x-text="'Rp ' + formatNumber(discount.total_profit)">
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <span x-text="getROIIcon(discount.roi_percentage)" class="text-xl"></span>
                                <span class="font-bold text-lg"
                                      :class="getROIColorClass(discount.roi_percentage)"
                                      x-text="discount.roi_percentage + '%'">
                                </span>
                            </div>
                        </td>
                    </tr>
                </template>

                {{-- Empty State --}}
                <tr x-show="performance.length === 0">
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <i data-lucide="bar-chart-3" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                        <p>Belum ada data penggunaan diskon</p>
                        <p class="text-sm mt-1">Data akan muncul setelah ada transaksi dengan diskon</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Legend --}}
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
        <div class="flex flex-wrap gap-6 text-sm">
            <div class="flex items-center gap-2">
                <span class="text-xl">🟢</span>
                <span class="text-slate-600">&gt; 500% = Sangat Efektif</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xl">🟡</span>
                <span class="text-slate-600">200-500% = Cukup Baik</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xl">🔴</span>
                <span class="text-slate-600">&lt; 200% = Perlu Review</span>
            </div>
        </div>
    </div>
</div>
