<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Diskon</h2>
        <p class="text-sm text-slate-600 mt-1">Kelola diskon produk dan kategori</p>
    </div>
    
    <button 
        @click="openModal('create')"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2 transition-colors"
    >
        <i data-lucide="plus" class="w-4 h-4"></i>
        Tambah Diskon
    </button>
</div>
