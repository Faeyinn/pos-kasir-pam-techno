<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Nama Diskon
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Tipe
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Nilai
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Target
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Periode
                </th>
                <th class="text-left px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Status
                </th>
                <th class="text-right px-6 py-3 text-xs font-medium text-slate-700 uppercase tracking-wider">
                    Aksi
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            <template x-for="discount in discounts" :key="discount.id">
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-900" x-text="discount.name"></div>
                    </td>
                    <td class="px-6 py-4">
                        <span 
                            class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                            :class="discount.type === 'percentage' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                            x-text="discount.type === 'percentage' ? 'Persentase' : 'Fixed'"
                        ></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-indigo-600" x-text="formatValue(discount)"></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600">
                            <div class="flex items-center gap-1">
                                <i data-lucide="tag" class="w-3 h-3"></i>
                                <span x-text="discount.target_type === 'product' ? 'Produk' : 'Tag'" class="font-medium"></span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5 line-clamp-1" x-text="getTargetNames(discount)"></div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-slate-600 space-y-1">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3 h-3 text-green-600"></i>
                                <span class="font-medium" x-text="formatDateTime(discount.start_date)"></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="calendar-check" class="w-3 h-3 text-red-600"></i>
                                <span class="font-medium" x-text="formatDateTime(discount.end_date)"></span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <button 
                            @click="toggleStatus(discount.id, discount.is_active)"
                            :class="discount.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1 rounded-full text-xs font-medium transition-colors"
                            x-text="discount.is_active ? 'Aktif' : 'Nonaktif'"
                        ></button>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <button 
                                @click="openModal('edit', discount)"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                title="Edit"
                            >
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <button 
                                @click="deleteDiscount(discount.id)"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Hapus"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
            
            <tr x-show="discounts.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                    <i data-lucide="percent" class="w-16 h-16 mx-auto mb-3 text-slate-300"></i>
                    <p class="text-slate-500 font-medium">Belum ada diskon</p>
                    <p class="text-sm text-slate-400 mt-1">Klik tombol "Tambah Diskon" untuk membuat diskon baru</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
