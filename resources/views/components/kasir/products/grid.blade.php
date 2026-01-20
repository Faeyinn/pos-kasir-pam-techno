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
            <x-kasir.products.card />
        </template>
    </div>

    <div x-show="viewMode === 'list'" class="flex flex-col gap-2 pb-24 lg:pb-6">

        <template x-for="i in (loading ? 10 : 0)" :key="'skeleton-list-' + i">
            <div class="bg-white rounded-xl px-5 py-4 border border-gray-100 shadow-sm flex items-center justify-between gap-4 animate-pulse min-h-[70px]">
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-slate-200 rounded w-1/3"></div>
                    <div class="h-3 bg-slate-200 rounded w-1/5"></div>
                </div>
                <div class="w-9 h-9 bg-slate-200 rounded-lg"></div>
            </div>
        </template>

        <template x-for="product in (!loading ? filteredProducts : [])" :key="'list-' + product.id">
            <div 
                @click="addToCart(product)"
                class="group bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:ring-1 hover:ring-blue-400 hover:bg-blue-50/30 shadow-sm transition-all cursor-pointer grid grid-cols-12 gap-0 overflow-hidden min-h-[72px]"
            >
                <div class="col-span-12 sm:col-span-5 p-4 flex flex-col justify-center border-b sm:border-b-0 sm:border-r border-gray-100 group-hover:border-blue-200/50 transition-colors">
                    <h3 class="font-bold text-gray-800 text-sm truncate group-hover:text-blue-700 transition-colors" x-text="product.name"></h3>
                    <template x-if="product.tags && product.tags.length > 0">
                        <div class="flex gap-1 mt-1.5 flex-wrap">
                            <template x-for="tag in product.tags" :key="'tag-' + product.id + '-' + tag.id">
                                <span 
                                    class="text-[10px] px-1.5 py-0.5 rounded-md border font-medium truncate max-w-[100px]" 
                                    :style="`background-color: ${tag.color}10; color: ${tag.color}; border-color: ${tag.color}20`"
                                    x-text="tag.name"
                                ></span>
                            </template>
                        </div>
                    </template>
                    <template x-if="!product.tags || product.tags.length === 0">
                       <span class="text-[10px] text-gray-400 mt-1 italic">Tidak ada tag</span>
                    </template>
                </div>

                <div class="col-span-10 sm:col-span-6 p-4 flex flex-col justify-center sm:pl-6 relative">
                    <div class="flex items-center gap-3 mb-1">
                        <span class="font-black text-gray-800 text-base" x-text="'Rp ' + formatNumber(product.price)"></span>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full border bg-gray-50 border-gray-200 text-gray-600 flex items-center gap-1">
                             <i data-lucide="package" class="w-3 h-3"></i>
                             <span x-text="'Stok: ' + (product.stock || 0)"></span>
                        </span>
                    </div>

                     <template x-if="product.wholesale > 0">
                        <div class="flex items-center gap-1.5 text-[10px] text-blue-700 w-fit">
                            <i data-lucide="tag" class="w-3 h-3 text-blue-500"></i>
                            <span class="bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100/50">
                                Grosir: Beli <span class="font-bold" x-text="product.wholesaleQtyPerUnit"></span> pcs 
                                <span class="text-blue-400 mx-0.5">|</span> 
                                <span class="font-bold">Rp <span x-text="formatNumber(product.wholesalePricePerPiece)"></span></span>/pcs
                            </span>
                        </div>
                    </template>
                </div>

                <div class="col-span-2 sm:col-span-1 flex items-center justify-center border-l border-gray-100 group-hover:border-blue-200/50 bg-gray-50/50 group-hover:bg-blue-600 transition-colors">
                     <button class="w-full h-full flex items-center justify-center text-gray-400 group-hover:text-white transition-colors">
                        <i data-lucide="plus" class="w-6 h-6"></i>
                    </button>
                </div>
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
