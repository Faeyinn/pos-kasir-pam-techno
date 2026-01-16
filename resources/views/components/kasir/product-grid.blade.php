<div class="flex-1 overflow-y-auto pr-1 custom-scrollbar">

    <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 pb-24 lg:pb-6">

        <template x-for="i in (loading ? 12 : 0)" :key="'skeleton-grid-' + i">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-3 sm:p-5 border border-gray-100 shadow-sm overflow-hidden animate-pulse">
                <div class="aspect-square bg-gradient-to-br from-slate-200 via-slate-150 to-slate-100 rounded-xl sm:rounded-2xl mb-3 sm:mb-4"></div>
                <div class="space-y-3">
                    <div class="space-y-2">
                        <div class="h-4 bg-slate-200 rounded w-3/4"></div>
                        <div class="h-3 bg-slate-200 rounded w-1/2"></div>
                    </div>
                    <div class="h-5 bg-slate-200 rounded w-2/3"></div>
                    <div class="h-9 bg-slate-200 rounded-lg"></div>
                </div>
            </div>
        </template>

        <template x-for="product in (!loading ? filteredProducts : [])" :key="'grid-' + product.id">
            <x-kasir.product-card />
        </template>
    </div>

    <div x-show="viewMode === 'list'" class="flex flex-col gap-3 pb-24 lg:pb-6">

        <template x-for="i in (loading ? 8 : 0)" :key="'skeleton-list-' + i">
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center gap-4 animate-pulse">
                <div class="w-20 h-20 bg-slate-200 rounded-xl shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-slate-200 rounded w-1/3"></div>
                    <div class="h-3 bg-slate-200 rounded w-1/4"></div>
                </div>
                <div class="w-32 h-10 bg-slate-200 rounded-lg"></div>
            </div>
        </template>

        <template x-for="product in (!loading ? filteredProducts : [])" :key="'list-' + product.id">
            <div class="group bg-white rounded-2xl p-3 sm:p-4 border border-gray-100 hover:border-blue-200 shadow-sm hover:shadow-md transition-all flex items-center gap-4">

                <div class="relative shrink-0 w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-xl overflow-hidden">
                    <template x-if="product.image">
                        <img :src="'/storage/' + product.image" :alt="product.name" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </template>
                    <template x-if="!product.image">
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <i data-lucide="image" class="w-8 h-8"></i>
                        </div>
                    </template>
                </div>

                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-800 text-sm sm:text-base truncate group-hover:text-blue-600 transition-colors" x-text="product.name"></h3>
                    <div class="flex flex-col gap-1 mt-1">
                        <span class="font-black text-gray-900 text-sm sm:text-base" x-text="'Rp ' + formatNumber(product.price)"></span>
                        <template x-if="product.wholesale > 0">
                            <div class="inline-flex flex-wrap items-center gap-1.5 text-[10px] sm:text-xs text-blue-800 bg-blue-50 px-2.5 py-1.5 rounded-lg border border-blue-100 w-fit">
                                <i data-lucide="tag" class="w-3 h-3 shrink-0 text-blue-600"></i>
                                <span>Grosir min. 1 <span x-text="product.wholesaleUnit"></span> (<span x-text="product.wholesaleQtyPerUnit"></span> pcs)</span>
                                <span class="font-bold text-blue-600">@ Rp <span x-text="formatNumber(product.wholesalePricePerPiece)"></span></span>
                            </div>
                        </template>
                    </div>
                </div>

                <button 
                    @click="addToCart(product)"
                    class="shrink-0 w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all active:scale-95"
                >
                    <i data-lucide="plus" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </button>
            </div>
        </template>
    </div>

    <template x-if="!loading && filteredProducts.length === 0">
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <i data-lucide="package-x" class="w-10 h-10 text-slate-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-700 mb-1">Produk tidak ditemukan</h3>
            <p class="text-sm text-slate-500">Coba ubah kata kunci atau kategori pencarian</p>
        </div>
    </template>
</div>
