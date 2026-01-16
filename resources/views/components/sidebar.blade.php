
<div 
    x-show="sidebarOpen" 
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden"
    @click="sidebarOpen = false"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
></div>

<aside 
    :class="{
        'w-64': sidebarOpen,
        'w-0 lg:w-20': !sidebarOpen,
        'fixed inset-y-0 left-0 z-50 shadow-2xl': window.innerWidth < 1024,
        'relative h-screen border-r border-white/5': window.innerWidth >= 1024
    }" 
    class="bg-slate-900 text-white transition-all duration-300 flex flex-col overflow-hidden shrink-0"
>

    <div class="h-24 px-4 flex items-center justify-between border-b border-white/5 shrink-0 relative">

        <div 
            class="flex items-center gap-3 transition-all duration-300 overflow-hidden"
            :class="sidebarOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0 lg:hidden'"
        >
            <img src="{{ asset('assets/logo-pure.png') }}" alt="Logo" class="w-9 h-9 object-contain shrink-0">
            <div class="flex flex-col">
                <h1 class="text-sm font-black tracking-tight leading-none text-white whitespace-nowrap">Pam Techno</h1>
                <p class="text-[9px] text-white/40 mt-1 font-bold uppercase tracking-widest">Cashier Pro</p>
            </div>
        </div>

        <button 
            @click="sidebarOpen = !sidebarOpen; if(window.innerWidth < 1024) mobileCartOpen = false" 
            class="p-2.5 hover:bg-white/10 rounded-xl transition-all text-white/70 active:scale-90 z-10"
            :class="!sidebarOpen ? 'mx-auto' : ''"
        >
            <i x-show="sidebarOpen" data-lucide="chevron-left" class="w-5 h-5"></i>
            <i x-show="!sidebarOpen" data-lucide="chevron-right" class="w-5 h-5" x-cloak></i>
        </button>
    </div>

    <div class="flex-1 relative w-full overflow-hidden">

        <nav class="absolute inset-0 p-3 space-y-4 overflow-y-auto custom-scrollbar pb-24">
            <div x-show="sidebarOpen" class="px-4 py-4 text-[10px] font-bold text-white/30 uppercase tracking-[0.2em] transition-opacity">Menu Utama</div>

            <button 
                @click="paymentType = 'retail'; $dispatch('payment-type-changed', 'retail'); if(window.innerWidth < 1024) sidebarOpen = false"
                class="w-full flex items-center rounded-2xl transition-all group relative overflow-hidden"
                :class="{
                    'bg-blue-600 text-white shadow-xl shadow-blue-900/40': paymentType === 'retail',
                    'hover:bg-white/5 text-white/60 hover:text-white': paymentType !== 'retail',
                    'p-4 gap-4': sidebarOpen,
                    'p-3 justify-center': !sidebarOpen
                }"
                :title="!sidebarOpen ? 'Retail' : ''"
            >
                <div :class="paymentType === 'retail' ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10'" class="p-2.5 rounded-xl transition-colors shrink-0 shadow-sm">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
                <div 
                    class="text-left transition-all duration-300 origin-left" 
                    x-show="sidebarOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                >
                    <div class="font-bold text-sm whitespace-nowrap">Retail / Eceran</div>
                    <div class="text-[10px] opacity-60 font-medium whitespace-nowrap">Harga standar</div>
                </div>
            </button>

            <button 
                @click="paymentType = 'wholesale'; $dispatch('payment-type-changed', 'wholesale'); if(window.innerWidth < 1024) sidebarOpen = false"
                class="w-full flex items-center rounded-2xl transition-all group relative overflow-hidden"
                :class="{
                    'bg-purple-600 text-white shadow-xl shadow-purple-900/40': paymentType === 'wholesale',
                    'hover:bg-white/5 text-white/60 hover:text-white': paymentType !== 'wholesale',
                    'p-4 gap-4': sidebarOpen,
                    'p-3 justify-center': !sidebarOpen
                }"
                :title="!sidebarOpen ? 'Grosir' : ''"
            >
                <div :class="paymentType === 'wholesale' ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10'" class="p-2.5 rounded-xl transition-colors shrink-0 shadow-sm">
                    <i data-lucide="package" class="w-5 h-5"></i>
                </div>
                <div 
                    class="text-left transition-all duration-300 origin-left" 
                    x-show="sidebarOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                >
                    <div class="font-bold text-sm whitespace-nowrap">Grosir / Partai</div>
                    <div class="text-[10px] opacity-60 font-medium whitespace-nowrap">Harga borongan</div>
                </div>
            </button>
        </nav>

        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-20 pointer-events-none w-full flex justify-center">
            <button 
                @click="openScanner()"
                class="group w-14 h-14 bg-gradient-to-tr from-blue-600 to-blue-400 rounded-full flex items-center justify-center shadow-lg shadow-blue-500/50 hover:scale-110 transition-transform duration-300 pointer-events-auto"
                title="Scan QR/Barcode"
            >

                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">

                    <path d="M4 8V6a2 2 0 0 1 2-2h2" />
                    <path d="M16 4h2a2 2 0 0 1 2 2v2" />
                    <path d="M20 16v2a2 2 0 0 1-2 2h-2" />
                    <path d="M8 20H6a2 2 0 0 1-2-2v-2" />

                    <path d="M10 10V9a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1" />
                    <path d="M14 14v1a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-1" />

                    <path d="M12 12h.01" /> 
                    <path d="M7 12h10" />
                </svg>
            </button>
        </div>
    </div>

    <div class="p-4 border-t border-white/5 bg-slate-900/50 backdrop-blur-md">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button 
                type="submit"
                class="w-full flex items-center gap-4 p-4 hover:bg-red-500/10 text-white/60 hover:text-red-400 rounded-2xl transition-all group"
                :class="!sidebarOpen ? 'justify-center' : ''"
            >
                <div class="p-2.5 bg-white/5 group-hover:bg-red-500/10 rounded-xl transition-colors shrink-0">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                </div>
                <span x-show="sidebarOpen" class="font-bold text-sm whitespace-nowrap">Keluar Sesi</span>
            </button>
        </form>
    </div>
</aside>
