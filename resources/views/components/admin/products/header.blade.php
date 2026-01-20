<div class="mb-6 flex items-end justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Produk</h2>
        <p class="text-sm text-slate-600 mt-1 mb-4">Kelola produk dan harga untuk sistem POS</p>
        
        <div class="relative group">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 w-4 h-4 transition-colors"></i>
            <input 
                type="text" 
                x-model="search"
                class="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 placeholder:text-slate-400 w-64 transition-all shadow-sm"
                placeholder="Cari produk..."
            >
        </div>
    </div>
    
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-4 px-4 py-2 bg-white rounded-xl border border-slate-200">
            <div class="text-center">
                <div class="text-xs text-slate-500">Total Produk</div>
                <div class="text-lg font-bold text-slate-900" x-text="products.length"></div>
            </div>
            <div class="w-px h-8 bg-slate-200"></div>
            <div class="text-center">
                <div class="text-xs text-slate-500">Aktif</div>
                <div class="text-lg font-bold text-green-600" x-text="products.filter(p => p.is_active).length"></div>
            </div>
            <div class="w-px h-8 bg-slate-200"></div>
            <div class="text-center">
                <div class="text-xs text-slate-500">Stok Rendah</div>
                <div class="text-lg font-bold text-red-600" x-text="products.filter(p => p.stock < 20).length"></div>
            </div>
        </div>

        <button
            type="button"
            x-on:click="openAddModal"
            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors shadow-lg shadow-indigo-600/30 text-sm"
        >
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Produk</span>
        </button>
    </div>
</div>
