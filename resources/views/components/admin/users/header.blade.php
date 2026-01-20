<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h3 class="text-2xl font-bold text-slate-800">Manajemen User</h3>
        <p class="text-slate-500 mt-1">Kelola daftar pengguna dan hak akses aplikasi</p>
    </div>
    
    <div class="flex gap-3">
        <div class="relative group">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 w-5 h-5 transition-colors"></i>
            <input 
                type="text" 
                x-model="search"
                class="pl-10 pr-4 py-2.5 bg-slate-50 border-none rounded-xl text-slate-700 text-sm focus:ring-2 focus:ring-indigo-100 placeholder:text-slate-400 w-full sm:w-64 transition-all"
                placeholder="Cari user..."
            >
        </div>
    </div>
</div>
