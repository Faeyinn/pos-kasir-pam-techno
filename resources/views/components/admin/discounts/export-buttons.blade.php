<div class="flex flex-col items-end gap-2 mt-3 mb-6 no-print" x-data="{ open: false }" @click.away="open = false">
    <div class="relative">
        <button 
            @click="open = !open"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all flex items-center gap-2 shadow-md active:scale-95"
        >
            <i data-lucide="download" class="w-4 h-4"></i>
            Export Laporan
            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''"></i>
        </button>

        <!-- Dropdown Menu -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden"
        >
            <div class="py-1">
                <button 
                    @click="window.print(); open = false"
                    class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-3 transition-colors"
                >
                    <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                    Export PDF (Print)
                </button>
                <div class="border-t border-slate-50"></div>
                <button 
                    @click="sendToGmail(); open = false"
                    :disabled="isSending"
                    class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-3 transition-colors disabled:opacity-50"
                >
                    <i x-show="!isSending" data-lucide="mail" class="w-4 h-4 text-indigo-500"></i>
                    <div x-show="isSending" class="w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin text-indigo-500" x-cloak></div>
                    <span x-text="isSending ? 'Sedang mengirim...' : 'Kirim ke Gmail Owner'"></span>
                </button>
            </div>
        </div>
    </div>
    
    <p class="text-[10px] text-slate-400 font-medium italic">
        *Tips: Pastikan centang "Background Graphics" di pengaturan cetak agar warna muncul
    </p>
</div>
