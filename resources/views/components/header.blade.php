<div class="bg-white border-b border-gray-100 p-6 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-6">

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

        <button 
            @click="$dispatch('open-history')"
            class="hidden md:flex items-center gap-2 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 rounded-xl border border-blue-100 transition-colors group"
            title="Riwayat Transaksi"
        >
            <i data-lucide="history" class="w-4 h-4 text-blue-600"></i>
            <span class="text-xs font-bold text-blue-600">Riwayat</span>
        </button>

        <div class="flex items-center gap-4 pl-6 border-l border-gray-100" x-data="{ open: false }">
            <div class="relative">
                <button 
                    @click="open = !open" 
                    @click.outside="open = false"
                    class="flex items-center gap-4 hover:bg-gray-50 p-2 -m-2 rounded-xl transition-colors"
                >
                    <div class="text-right hidden sm:block">
                        <div class="font-bold text-sm text-gray-900">{{ Auth::user()->name }}</div>
                        <div class="text-blue-600 text-[10px] font-bold uppercase tracking-widest">{{ Auth::user()->role === 'admin' ? 'Owner / Admin' : (Auth::user()->role === 'master' ? 'Master Access' : 'Petugas Kasir') }}</div>
                    </div>
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-black shadow-lg shadow-blue-200 uppercase">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </button>

                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    class="absolute right-0 top-full mt-4 w-56 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden"
                    style="display: none;"
                >
                    <div class="p-2">
                        <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            Lihat Profil
                        </a>
                        
                        @if(Auth::user()->role === 'master')
                        <div class="h-px bg-gray-100 my-1"></div>
                        <form action="{{ route('role.set') }}" method="POST">
                            @csrf
                            <input type="hidden" name="role" value="admin">
                             <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-xl transition-colors text-left">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                Switch to Admin
                            </button>
                        </form>
                        <div class="h-px bg-gray-100 my-1"></div>
                        @else
                        <div class="h-px bg-gray-100 my-1"></div>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-500 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors text-left">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
