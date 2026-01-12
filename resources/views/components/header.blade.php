<div class="bg-white border-b border-gray-100 p-6 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-6">
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-3 bg-gray-50 rounded-2xl text-gray-400 hover:text-blue-600 transition-colors">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <div>
            <h2 class="text-2xl font-black text-gray-900 leading-none">
                @yield('page_title', 'Sesi Kasir')
            </h2>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] mt-2">
                {{ Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </p>
        </div>
    </div>
    
    <div class="flex items-center gap-4">
        <div class="hidden md:flex flex-col items-end">
            <div class="flex items-center gap-2 px-4 py-2 bg-green-50 rounded-full border border-green-100">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-[10px] font-black text-green-600 uppercase tracking-widest">Sistem Online</span>
            </div>
        </div>



        <div class="flex items-center gap-4 pl-6 border-l border-gray-100">
            <div class="text-right hidden sm:block">
                <div class="font-bold text-sm text-gray-900">{{ Auth::user()->name }}</div>
                <div class="text-blue-600 text-[10px] font-bold uppercase tracking-widest">{{ Auth::user()->role === 'admin' ? 'Owner / Admin' : 'Petugas Kasir' }}</div>
            </div>
            <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-black shadow-lg shadow-blue-200 uppercase">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
        </div>
    </div>
</div>
