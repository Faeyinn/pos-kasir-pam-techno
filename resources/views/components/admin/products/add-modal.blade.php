{{-- Product Add Modal --}}
<x-ui.modal name="product-add" max-width="3xl">
    <div class="p-6">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-900">Tambah Produk Baru</h3>
            <button 
                type="button"
                x-on:click="$dispatch('close-product-add')"
                class="text-slate-400 hover:text-slate-600 transition-colors"
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
            <div class="space-y-4">
                {{-- Nama Produk --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text"
                        x-model="addForm.name"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="Masukkan nama produk"
                        required
                    >
                </div>

                {{-- Harga Jual & Harga Modal --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Harga Jual (Eceran) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                            <input 
                                type="number"
                                x-model.number="addForm.price"
                                class="w-full pl-12 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                placeholder="0"
                                min="0"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Harga Modal <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                            <input 
                                type="number"
                                x-model.number="addForm.cost_price"
                                class="w-full pl-12 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                placeholder="0"
                                min="0"
                                required
                            >
                        </div>
                        <p class="mt-1.5 text-xs text-slate-500">
                            <i data-lucide="info" class="w-3 h-3 inline mr-1"></i>
                            Digunakan untuk menghitung laba, tidak terlihat oleh kasir
                        </p>
                    </div>
                </div>

                {{-- Margin Info (Real-time) --}}
                <div 
                    x-show="addForm.price > 0 && addForm.cost_price > 0"
                    class="p-3 bg-slate-50 rounded-xl border border-slate-200"
                >
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Margin:</span>
                        <span 
                            class="font-semibold"
                            :class="((addForm.price - addForm.cost_price) / addForm.price * 100) > 0 ? 'text-green-600' : 'text-red-600'"
                            x-text="'Rp ' + (addForm.price - addForm.cost_price).toLocaleString('id-ID') + ' (' + (((addForm.price - addForm.cost_price) / addForm.price * 100).toFixed(1)) + '%)'">
                        </span>
                    </div>
                </div>

                {{-- Stok Awal --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Stok Awal <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number"
                        x-model.number="addForm.stock"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="0"
                        min="0"
                        required
                    >
                </div>

                {{-- Harga Grosir (Multi Satuan - Opsional) --}}
                <div class="border-t border-slate-200 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-slate-900">Harga Grosir (Opsional)</h4>
                        <button
                            type="button"
                            x-on:click="addWholesaleUnit('add')"
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-medium text-slate-700 transition-colors flex items-center gap-2"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Tambah Satuan
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(u, idx) in addForm.satuan_grosir" :key="idx">
                            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50">
                                <div class="grid grid-cols-12 gap-3 items-end">
                                    <div class="col-span-12 md:col-span-5">
                                        <label class="block text-sm font-medium text-slate-700 mb-2">
                                            Nama Satuan <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            x-model="u.nama_satuan"
                                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                            placeholder="Pack, Dus, Karton"
                                        >
                                    </div>

                                    <div class="col-span-12 md:col-span-3">
                                        <label class="block text-sm font-medium text-slate-700 mb-2">
                                            Isi per Satuan <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            x-model.number="u.jumlah_per_satuan"
                                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                            placeholder="6"
                                            min="2"
                                        >
                                    </div>

                                    <div class="col-span-12 md:col-span-3">
                                        <label class="block text-sm font-medium text-slate-700 mb-2">
                                            Harga per Satuan <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                                            <input
                                                type="number"
                                                x-model.number="u.harga_jual"
                                                class="w-full pl-12 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                                placeholder="0"
                                                min="0"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-span-12 md:col-span-1 flex justify-end">
                                        <button
                                            type="button"
                                            x-on:click="removeWholesaleUnit('add', idx)"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors"
                                            title="Hapus satuan"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">
                                    Contoh: Jika satuan dasar adalah Pcs, maka Pack (6) berarti 1 Pack berisi 6 Pcs.
                                </p>
                            </div>
                        </template>

                        <template x-if="!addForm.satuan_grosir || addForm.satuan_grosir.length === 0">
                            <div class="p-4 rounded-2xl border border-dashed border-slate-300 text-sm text-slate-500">
                                Belum ada satuan grosir. Klik "Tambah Satuan" untuk menambahkan.
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Tags --}}
                <x-admin.shared.tag-selector modelName="addForm.tag_ids" />

                {{-- Status --}}
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input 
                            type="checkbox"
                            x-model="addForm.is_active"
                            class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-2 focus:ring-indigo-500"
                        >
                        <span class="text-sm font-medium text-slate-700">Produk Aktif</span>
                    </label>
                    <p class="mt-1.5 ml-8 text-xs text-slate-500">
                        Produk yang tidak aktif tidak akan muncul di kasir
                    </p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-slate-200">
                <button 
                    type="button"
                    x-on:click="$dispatch('close-product-add')"
                    class="px-6 py-2.5 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl font-medium transition-colors"
                    :disabled="addLoading"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors flex items-center gap-2"
                    :disabled="addLoading"
                >
                    <i data-lucide="plus-circle" class="w-4 h-4" x-show="!addLoading"></i>
                    <svg x-show="addLoading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="addLoading ? 'Menyimpan...' : 'Tambah Produk'"></span>
                </button>
            </div>
        </form>
    </div>
</x-ui.modal>
