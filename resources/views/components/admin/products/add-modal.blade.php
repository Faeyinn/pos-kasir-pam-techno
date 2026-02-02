{{-- Product Add Modal --}}
<x-ui.modal name="product-add" max-width="3xl">
    <div class="p-6" x-data="{ costPriceMode: 'manual', costPriceLargest: 0 }">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                    <i data-lucide="plus-circle" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 leading-none">Tambah Produk Baru</h3>
                    <p class="text-xs text-slate-500 mt-1.5 font-medium">Lengkapi detail produk untuk menambahkannya ke stok.</p>
                </div>
            </div>
            <button 
                type="button"
                x-on:click="$dispatch('close-product-add')"
                class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all active:scale-95"
            >
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        {{-- Error Alert --}}
        <div 
            x-show="addErrors && Object.keys(addErrors).length > 0"
            x-cloak
            class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl"
        >
            <div class="flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <h4 class="font-semibold text-red-900 mb-1">Terjadi Kesalahan</h4>
                    <ul class="text-sm text-red-700 space-y-1">
                        <template x-for="(error, field) in addErrors" :key="field">
                            <li x-text="Array.isArray(error) ? error[0] : error"></li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form x-on:submit.prevent="createProduct">
            <div class="space-y-6">
                {{-- Nama Produk & Tag Selector --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Produk <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <i data-lucide="package" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                            <input 
                                type="text"
                                x-model="addForm.name"
                                class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-900"
                                placeholder="Masukkan nama produk..."
                                required
                            >
                        </div>
                        <p class="mt-2 text-[10px] text-slate-400">Pastikan nama produk jelas dan mudah dicari.</p>
                    </div>
                    <div class="md:col-span-1">
                        <x-admin.shared.tag-selector modelName="addForm.tag_ids" />
                    </div>
                </div>

                {{-- Barcode Dasar & Stok Dasar --}}
                <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                            <i data-lucide="package" class="w-4 h-4 text-white"></i>
                        </div>
                        <h4 class="font-bold text-slate-900">Satuan Dasar (Pieces/Eceran)</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2">Barcode (Scan)</label>
                            <div class="relative group">
                                <i data-lucide="barcode" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                                <input 
                                    type="text"
                                    x-model="addForm.barcode"
                                    class="w-full pl-11 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors bg-white font-mono text-sm"
                                    placeholder="Scan / Ketik Barcode"
                                >
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2">Stok Awal</label>
                            <input 
                                type="number"
                                x-model.number="addForm.stock"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors bg-white"
                                placeholder="0"
                                min="0"
                                required
                            >
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-2">Harga Jual (Eceran)</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                                <input 
                                    type="number"
                                    x-model.number="addForm.price"
                                    class="w-full pl-11 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors bg-white font-bold"
                                    placeholder="0"
                                    min="0"
                                    required
                                >
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Harga Grosir (Multi Satuan) --}}
                <div class="border-t border-slate-200 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                                <i data-lucide="layers" class="w-4 h-4 text-white"></i>
                            </div>
                            <h4 class="font-bold text-slate-900">Satuan Grosir (Paket/Dus/Lainnya)</h4>
                        </div>
                        <button
                            type="button"
                            x-on:click="addWholesaleUnit('add')"
                            class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 rounded-xl text-sm font-bold text-emerald-700 transition-all flex items-center gap-2 active:scale-95"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Tambah Satuan
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(u, idx) in addForm.satuan_grosir" :key="idx">
                            <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-sm relative group hover:border-emerald-200 transition-colors">
                                <button
                                    type="button"
                                    x-on:click="removeWholesaleUnit('add', idx)"
                                    class="absolute -top-3 -right-3 w-8 h-8 bg-white border border-slate-200 text-red-500 hover:bg-red-50 rounded-full flex items-center justify-center transition-all shadow-md active:scale-90"
                                    title="Hapus satuan"
                                >
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="md:col-span-1">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nama Satuan</label>
                                        <input
                                            type="text"
                                            x-model="u.nama_satuan"
                                            class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm font-bold"
                                            placeholder="Dus / Pack / Lusin"
                                        >
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Isi (Pcs)</label>
                                        <input
                                            type="number"
                                            x-model.number="u.jumlah_per_satuan"
                                            class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm font-bold"
                                            placeholder="Isi 24"
                                            min="2"
                                        >
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Harga Jual</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">Rp</span>
                                            <input
                                                type="number"
                                                x-model.number="u.harga_jual"
                                                class="w-full pl-8 pr-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm font-bold text-emerald-600"
                                                placeholder="0"
                                                min="0"
                                            >
                                        </div>
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Barcode Unit</label>
                                        <input
                                            type="text"
                                            x-model="u.barcode"
                                            class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors text-sm font-mono"
                                            placeholder="Barcode Baris Ini"
                                        >
                                    </div>
                                </div>
                                
                                <div class="mt-3 flex items-center justify-between">
                                    <p class="text-[10px] text-slate-400 italic">
                                        * <span x-text="u.nama_satuan || '...' "></span> berisi <span class="font-bold text-slate-600" x-text="u.jumlah_per_satuan || 0"></span> Pcs.
                                    </p>
                                    <template x-if="u.harga_jual > 0 && u.jumlah_per_satuan > 0">
                                        <div class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                                            Rp <span x-text="formatNumber(Math.round(u.harga_jual / u.jumlah_per_satuan))"></span> / Pcs
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="!addForm.satuan_grosir || addForm.satuan_grosir.length === 0">
                            <div class="p-8 rounded-2xl border-2 border-dashed border-slate-200 text-center">
                                <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="info" class="w-6 h-6 text-slate-300"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-500">Belum ada satuan grosir.</p>
                                <p class="text-[11px] text-slate-400 mt-1">Gunakan satuan grosir jika Anda menjual barang dalam bentuk renteng, pack, atau dus.</p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Harga Modal Logic --}}
                <div class="pt-6 border-t border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center">
                                <i data-lucide="calculator" class="w-4 h-4 text-white"></i>
                            </div>
                            <h4 class="font-bold text-slate-900">Pengaturan Harga Modal</h4>
                        </div>
                    </div>

                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200">
                        <div class="flex flex-wrap gap-4 mb-5">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="modal_mode" value="manual" x-model="costPriceMode" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">Input Manual (Per Piece)</span>
                            </label>
                            <template x-if="addForm.satuan_grosir && addForm.satuan_grosir.length > 0">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="modal_mode" value="auto" x-model="costPriceMode" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                    <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">Otomatis (Berdasarkan Satuan Grosir)</span>
                                </label>
                            </template>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <template x-if="costPriceMode === 'manual'">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Harga Modal Dasar (Pcs)</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                                        <input 
                                            type="number"
                                            x-model.number="addForm.cost_price"
                                            class="w-full pl-11 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-bold"
                                            placeholder="0"
                                            min="0"
                                            required
                                        >
                                    </div>
                                </div>
                            </template>

                            <template x-if="costPriceMode === 'auto' && getLargestUnit('add')">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2" x-text="`Harga Modal per 1 ${getLargestUnit('add').nama_satuan}`"></label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                                            <input 
                                                type="number"
                                                x-model.number="costPriceLargest"
                                                @input="calculateCostPriceFromLargest('add', $event.target.value)"
                                                class="w-full pl-11 pr-4 py-3 border border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-bold bg-white"
                                                placeholder="0"
                                                min="0"
                                            >
                                        </div>
                                        <p class="mt-2 text-[10px] text-slate-400 italic">
                                            * Sistem otomatis membagi harga modal <span x-text="getLargestUnit('add').nama_satuan"></span> dengan isi (<span x-text="getLargestUnit('add').jumlah_per_satuan"></span>) untuk mendapatkan modal per Pcs.
                                        </p>
                                    </div>
                                    <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs font-bold text-slate-500">Hasil Modal/Pcs:</span>
                                            <span class="text-sm font-black text-indigo-600" x-text="'Rp ' + formatNumber(addForm.cost_price)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Margin Summary --}}
                            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm h-full flex flex-col justify-center">
                                <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Estimasi Keuntungan / Pcs</h5>
                                <div class="flex items-end justify-between">
                                    <div>
                                        <div class="text-2xl font-black" :class="(addForm.price - addForm.cost_price) > 0 ? 'text-emerald-600' : 'text-red-500'" x-text="'Rp ' + formatNumber(addForm.price - addForm.cost_price)"></div>
                                        <div class="text-[11px] font-bold mt-1" :class="(addForm.price - addForm.cost_price) > 0 ? 'text-emerald-500' : 'text-red-400'" x-text="addForm.price > 0 ? (((addForm.price - addForm.cost_price) / addForm.price * 100).toFixed(1) + '% Profit Margin') : '0% Matrix'"></div>
                                    </div>
                                    <div x-show="(addForm.price - addForm.cost_price) > 0" class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                                        <i data-lucide="trending-up" class="w-6 h-6"></i>
                                    </div>
                                    <div x-show="(addForm.price - addForm.cost_price) <= 0" class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                                        <i data-lucide="trending-down" class="w-6 h-6"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex items-center justify-between px-2">
                    <div>
                        <h5 class="text-sm font-bold text-slate-900">Status Aktif</h5>
                        <p class="text-[11px] text-slate-400">Tentukan apakah produk ini muncul di menu kasir</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="addForm.is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-indigo-500/10 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-slate-200">
                <button 
                    type="button"
                    x-on:click="$dispatch('close-product-add')"
                    class="px-8 py-3 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl font-bold text-sm transition-all active:scale-95"
                    :disabled="addLoading"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm transition-all flex items-center gap-2 shadow-lg shadow-indigo-200 active:scale-95 disabled:opacity-50"
                    :disabled="addLoading"
                >
                    <i data-lucide="check-circle-2" class="w-4 h-4" x-show="!addLoading"></i>
                    <svg x-show="addLoading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="addLoading ? 'Menyimpan...' : 'Simpan Produk'"></span>
                </button>
            </div>
        </form>
    </div>
</x-ui.modal>
