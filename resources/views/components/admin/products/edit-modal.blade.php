{{-- Product Edit Modal --}}
<x-ui.modal name="product-edit" max-width="3xl">
    <div class="p-6">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-900">Edit Produk</h3>
            <button 
                type="button"
                x-on:click="$dispatch('close-product-edit')"
                class="text-slate-400 hover:text-slate-600 transition-colors"
            >
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        {{-- Error Alert --}}
        <div 
            x-show="errors && Object.keys(errors).length > 0"
            x-cloak
            class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl"
        >
            <div class="flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <h4 class="font-semibold text-red-900 mb-1">Terjadi Kesalahan</h4>
                    <ul class="text-sm text-red-700 space-y-1">
                        <template x-for="(error, field) in errors" :key="field">
                            <li x-text="Array.isArray(error) ? error[0] : error"></li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form x-on:submit.prevent="updateProduct">
            <div class="space-y-4">
                {{-- Nama Produk --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text"
                        x-model="form.name"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="Masukkan nama produk"
                        required
                    >
                </div>

                {{-- Harga Jual & Harga Modal --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Harga Jual <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                            <input 
                                type="number"
                                x-model.number="form.price"
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
                                x-model.number="form.cost_price"
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
                    x-show="form.price > 0 && form.cost_price > 0"
                    class="p-3 bg-slate-50 rounded-xl border border-slate-200"
                >
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Margin:</span>
                        <span 
                            class="font-semibold"
                            :class="((form.price - form.cost_price) / form.price * 100) > 0 ? 'text-green-600' : 'text-red-600'"
                            x-text="'Rp ' + (form.price - form.cost_price).toLocaleString('id-ID') + ' (' + (((form.price - form.cost_price) / form.price * 100).toFixed(1)) + '%)'">
                        </span>
                    </div>
                </div>

                {{-- Stok --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Stok <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number"
                        x-model.number="form.stock"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="0"
                        min="0"
                        required
                    >
                </div>

                {{-- Harga Grosir (Optional) --}}
                <div class="border-t border-slate-200 pt-4">
                    <h4 class="font-semibold text-slate-900 mb-3">Harga Grosir (Opsional)</h4>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Harga Grosir
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                                <input 
                                    type="number"
                                    x-model.number="form.wholesale"
                                    class="w-full pl-12 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    placeholder="0"
                                    min="0"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Satuan
                            </label>
                            <input 
                                type="text"
                                x-model="form.wholesale_unit"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                placeholder="Dus, Karung, Pack"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Qty per Unit
                            </label>
                            <input 
                                type="number"
                                x-model.number="form.wholesale_qty_per_unit"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                placeholder="1"
                                min="1"
                            >
                        </div>
                    </div>
                </div>

                {{-- Tags --}}
                <x-admin.shared.tag-selector modelName="form.tag_ids" />

                {{-- Status --}}
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input 
                            type="checkbox"
                            x-model="form.is_active"
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
                    x-on:click="$dispatch('close-product-edit')"
                    class="px-6 py-2.5 text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl font-medium transition-colors"
                    :disabled="loading"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-colors flex items-center gap-2"
                    :disabled="loading"
                >
                    <i data-lucide="save" class="w-4 h-4" x-show="!loading"></i>
                    <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                </button>
            </div>
        </form>
    </div>
</x-ui.modal>
