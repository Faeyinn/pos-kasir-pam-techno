@props(['products', 'tags'])

<div 
    x-show="showModal" 
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div 
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
        @click="closeModal"
    ></div>

    {{-- Modal --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full"
            @click.stop
        >
            {{-- Header --}}
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">
                    <span x-text="modalMode === 'create' ? 'Tambah Diskon Baru' : 'Edit Diskon'"></span>
                </h3>
                <button 
                    @click="closeModal"
                    class="text-slate-400 hover:text-slate-600 transition-colors"
                >
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-4">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Diskon <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="formData.name"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Contoh: Diskon Akhir Tahun"
                    >
                </div>

                {{-- Type & Value --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Tipe Diskon <span class="text-red-500">*</span>
                        </label>
                        <select 
                            x-model="formData.type"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Fixed (Rp)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Nilai <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                x-model="formData.value"
                                min="0"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :placeholder="formData.type === 'percentage' ? 'Contoh: 10' : 'Contoh: 5000'"
                            >
                            <div class="absolute right-3 top-2.5 text-slate-500 text-sm" x-text="formData.type === 'percentage' ? '%' : 'Rp'"></div>
                        </div>
                    </div>
                </div>

                {{-- Unified Product Selection with Smart Search --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-semibold text-slate-700">
                            Pilih Produk Target <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[10px] text-slate-400 uppercase font-bold" x-text="formData.target_ids.length + ' dipilih'"></span>
                    </div>

                    {{-- Unified Search Box + Selection Controls (60/40 Split) --}}
                    <div class="flex gap-2">
                        <div class="relative w-[60%]">
                            <input 
                                type="text" 
                                x-model="productSearch"
                                placeholder="Cari nama atau tag (ex: Sembako)..."
                                class="w-full px-4 py-2 pl-10 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            >
                            <div class="absolute left-3 top-2.5 text-slate-400">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div class="flex flex-1 gap-2">
                            <button
                                type="button"
                                @click="selectAllFiltered()"
                                class="flex-1 px-3 py-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition-colors text-xs font-bold uppercase tracking-tight"
                            >
                                Semua
                            </button>
                            <button
                                type="button"
                                @click="formData.target_ids = []"
                                class="flex-1 px-3 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors text-xs font-bold uppercase tracking-tight"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                    
                    <div 
                        class="border border-slate-200 rounded-xl max-h-44 overflow-y-auto custom-scrollbar shadow-inner bg-slate-50/50"
                        style="max-height: 180px;"
                    >
                        <template x-for="product in filteredProducts" :key="product.id">
                            <label 
                                class="flex items-center space-x-3 py-3 px-4 hover:bg-white border-b border-slate-100 last:border-0 cursor-pointer transition-colors group"
                            >
                                <input 
                                    type="checkbox" 
                                    :value="product.id" 
                                    x-model="formData.target_ids"
                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                >
                                <div class="flex flex-1 items-center justify-between min-w-0">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <span class="text-sm font-bold text-slate-800 truncate" x-text="product.name"></span>
                                        <span class="text-slate-300 text-xs">•</span>
                                        <span class="text-sm font-black text-indigo-600" x-text="'Rp ' + formatNumber(product.price)"></span>
                                        
                                        <template x-if="product.tags && product.tags.length > 0">
                                            <div class="flex items-center gap-1 min-w-0">
                                                <span class="text-slate-300 text-xs">•</span>
                                                <div class="flex gap-1 overflow-hidden">
                                                    <template x-for="(tag, index) in product.tags" :key="tag.id">
                                                        <span class="text-[10px] text-slate-500 font-medium" x-text="tag.name + (index < product.tags.length - 1 ? ',' : '')"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <template x-if="formData.target_ids.includes(product.id)">
                                        <div class="text-green-500 shrink-0">
                                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                        </div>
                                    </template>
                                </div>
                            </label>
                        </template>
                        
                        <div x-show="filteredProducts.length === 0" class="py-12 text-center text-slate-500">
                             <p class="text-sm">Produk tidak ditemukan</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Waktu Mulai <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                x-ref="startDate"
                                x-init="
                                    const fp = flatpickr($refs.startDate, { 
                                        enableTime: true, 
                                        dateFormat: 'Y-m-d H:i',
                                        altInput: true,
                                        altFormat: 'd/m/Y H:i',
                                        allowInput: true,
                                        onChange: (selectedDates, dateStr) => { formData.start_date = dateStr }
                                    });
                                    $watch('formData.start_date', value => fp.setDate(value));
                                "
                                :value="formData.start_date"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                placeholder="dd/mm/yyyy jam:menit"
                            >
                            <div class="absolute right-3 top-2.5 text-slate-400 pointer-events-none">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Waktu Berakhir <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                x-ref="endDate"
                                x-init="
                                    const fp = flatpickr($refs.endDate, { 
                                        enableTime: true, 
                                        dateFormat: 'Y-m-d H:i',
                                        altInput: true,
                                        altFormat: 'd/m/Y H:i',
                                        allowInput: true,
                                        onChange: (selectedDates, dateStr) => { formData.end_date = dateStr }
                                    });
                                    $watch('formData.end_date', value => fp.setDate(value));
                                "
                                :value="formData.end_date"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                placeholder="dd/mm/yyyy jam:menit"
                            >
                            <div class="absolute right-3 top-2.5 text-slate-400 pointer-events-none">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Simple Auto-Activation Toggle - Aligned Right --}}
                <div class="flex items-center justify-end gap-3 py-2 px-1">
                    <label class="text-sm font-medium text-slate-700">
                        Aktivasi Otomatis
                    </label>
                    
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            x-model="formData.auto_activate" 
                            class="sr-only peer"
                        >
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 p-6 border-t border-slate-200">
                <button 
                    @click="closeModal"
                    class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                >
                    Batal
                </button>
                <button 
                    @click="saveDiscount"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
                >
                    <span x-text="modalMode === 'create' ? 'Buat Diskon' : 'Simpan Perubahan'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
