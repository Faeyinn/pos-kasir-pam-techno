<div class="flex-1 overflow-y-auto pr-1 custom-scrollbar">
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-6 pb-24 md:pb-6">
        <!-- Loading Skeleton -->
        <template x-for="i in (loading ? 12 : 0)" :key="'skeleton-' + i">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-3 sm:p-5 border border-gray-100 shadow-sm overflow-hidden animate-pulse">
                <!-- Image Skeleton -->
                <div class="aspect-square bg-gradient-to-br from-slate-200 via-slate-150 to-slate-100 rounded-xl sm:rounded-2xl mb-3 sm:mb-4"></div>
                
                <!-- Content Skeleton -->
                <div class="space-y-3">
                    <!-- Product Name -->
                    <div class="space-y-2">
                        <div class="h-4 bg-slate-200 rounded w-3/4"></div>
                        <div class="h-3 bg-slate-200 rounded w-1/2"></div>
                    </div>
                    
                    <!-- Price -->
                    <div class="h-5 bg-slate-200 rounded w-2/3"></div>
                    
                    <!-- Stock Badge -->
                    <div class="flex gap-2">
                        <div class="h-5 bg-slate-200 rounded-full w-16"></div>
                    </div>
                    
                    <!-- Button -->
                    <div class="h-9 bg-slate-200 rounded-lg"></div>
                </div>
            </div>
        </template>
        
        <!-- Actual Products -->
        <template x-for="product in (!loading ? filteredProducts : [])" :key="product.id">
            <x-kasir.product-card />
        </template>
        
        <!-- Empty State -->
        <template x-if="!loading && filteredProducts.length === 0">
            <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="package-x" class="w-10 h-10 text-slate-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-1">Produk tidak ditemukan</h3>
                <p class="text-sm text-slate-500">Coba ubah kata kunci atau kategori pencarian</p>
            </div>
        </template>
    </div>
</div>
