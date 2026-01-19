{{-- Product Table Component --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    {{-- Table Header --}}
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
        <h3 class="font-semibold text-slate-900">Daftar Produk</h3>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Nama Produk
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Harga Eceran
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Harga Grosir
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Stok
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Tag
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <template x-for="product in paginatedProducts" :key="product.id">
                    <tr class="hover:bg-slate-50 transition-colors">
                        {{-- Nama Produk --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="package" class="w-5 h-5 text-slate-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-slate-900" x-text="product.name"></div>
                                    <div 
                                        x-show="product.wholesale > 0" 
                                        class="text-xs text-purple-600 font-medium mt-0.5"
                                    >
                                        <i data-lucide="layers" class="w-3 h-3 inline"></i>
                                        Grosir tersedia
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Harga Eceran --}}
                        <td class="px-6 py-4">
                            <template x-if="getActiveDiscount(product)">
                                <div>
                                    {{-- Discount Badge --}}
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                            <i data-lucide="percent" class="w-3 h-3"></i>
                                            <span x-text="getDiscountPercentage(product) + '%'"></span>
                                        </span>
                                    </div>
                                    {{-- Original Price (Strikethrough) --}}
                                    <div class="text-xs text-slate-400 line-through" x-text="'Rp ' + product.price.toLocaleString('id-ID')"></div>
                                    {{-- Discounted Price --}}
                                    <div class="text-sm font-bold text-green-600" x-text="'Rp ' + Math.round(getDiscountedPrice(product)).toLocaleString('id-ID')"></div>
                                    <div class="text-xs text-slate-500">per pcs</div>
                                </div>
                            </template>
                            <template x-if="!getActiveDiscount(product)">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900" x-text="'Rp ' + product.price.toLocaleString('id-ID')"></div>
                                    <div class="text-xs text-slate-500">per pcs</div>
                                </div>
                            </template>
                        </td>

                        {{-- Harga Grosir --}}
                        <td class="px-6 py-4">
                            <template x-if="product.wholesale > 0">
                                <div>
                                    <div class="text-sm font-semibold text-purple-700" x-text="'Rp ' + product.wholesale.toLocaleString('id-ID')"></div>
                                    <div class="text-xs text-purple-600" x-text="'per ' + product.wholesale_unit + ' (' + product.wholesale_qty_per_unit + ' pcs)'"></div>
                                </div>
                            </template>
                            <template x-if="!product.wholesale || product.wholesale === 0">
                                <div class="text-sm text-slate-400">-</div>
                            </template>
                        </td>

                        {{-- Stok --}}
                        <td class="px-6 py-4">
                            <div 
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-medium whitespace-nowrap"
                                :class="{
                                    'bg-red-100 text-red-700': product.stock < 20,
                                    'bg-yellow-100 text-yellow-700': product.stock >= 20 && product.stock < 50,
                                    'bg-green-100 text-green-700': product.stock >= 50
                                }"
                            >
                                <i data-lucide="package-2" class="w-3.5 h-3.5"></i>
                                <span x-text="product.stock + ' pcs'"></span>
                            </div>
                        </td>

                        {{-- Tag --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                <template x-if="!product.tags || product.tags.length === 0">
                                    <span class="text-sm text-slate-400">-</span>
                                </template>
                                <template x-if="product.tags && product.tags.length > 0">
                                    <template x-for="tag in product.tags.slice(0, 2)" :key="tag.id">
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border"
                                            :style="`background-color: ${tag.color}15; color: ${tag.color}; border-color: ${tag.color}30`"
                                            x-text="tag.name"
                                        ></span>
                                    </template>
                                </template>
                                <template x-if="product.tags && product.tags.length > 2">
                                    <span 
                                        class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs font-medium"
                                        x-text="'+' + (product.tags.length - 2)"
                                    ></span>
                                </template>
                            </div>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button 
                                    type="button"
                                    x-on:click="editProduct(product.id)"
                                    class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                    title="Edit Produk"
                                >
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <button 
                                    type="button"
                                    x-on:click="deleteProduct(product.id, product.name)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Hapus Produk"
                                >
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>

                {{-- Empty State --}}
                {{-- Empty State --}}
                <template x-if="filteredProducts.length === 0">
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            {{-- Case: No Products At All --}}
                            <template x-if="products.length === 0">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center">
                                        <i data-lucide="package-x" class="w-8 h-8 text-slate-400"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-1">Belum Ada Produk</h4>
                                        <p class="text-sm text-slate-500">Tambahkan produk pertama Anda untuk memulai</p>
                                    </div>
                                </div>
                            </template>
                            
                            {{-- Case: Search Result Empty --}}
                            <template x-if="products.length > 0">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center">
                                        <i data-lucide="search-x" class="w-8 h-8 text-slate-400"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-1">Produk Tidak Ditemukan</h4>
                                        <p class="text-sm text-slate-500">Coba kata kunci lain atau reset pencarian</p>
                                    </div>
                                </div>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div 
        x-show="totalPages > 1" 
        class="px-6 py-4 border-t border-slate-200 flex items-center justify-between bg-slate-50"
    >
        <div class="text-sm text-slate-600">
            Menampilkan 
            <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span>
            - 
            <span class="font-medium" x-text="Math.min(currentPage * perPage, filteredProducts.length)"></span>
            dari 
            <span class="font-medium" x-text="filteredProducts.length"></span>
            produk
        </div>

        <div class="flex items-center gap-2">
            {{-- Previous Button --}}
            <button
                @click="prevPage()"
                :disabled="currentPage === 1"
                :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-200'"
                class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 transition-colors"
            >
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>

            {{-- Page Numbers --}}
            <template x-for="page in totalPages" :key="page">
                <button
                    @click="goToPage(page)"
                    :class="currentPage === page ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100'"
                    class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm font-medium transition-colors"
                    x-text="page"
                ></button>
            </template>

            {{-- Next Button --}}
            <button
                @click="nextPage()"
                :disabled="currentPage === totalPages"
                :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-200'"
                class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 transition-colors"
            >
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
