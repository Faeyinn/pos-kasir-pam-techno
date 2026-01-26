<div class="flex flex-col items-end gap-2 mt-3 mb-6 no-print">
    <button 
        @click="window.print()"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all flex items-center gap-2 shadow-md active:scale-95"
    >
        <i data-lucide="printer" class="w-4 h-4"></i>
        Export PDF Laporan
    </button>
    <p class="text-[10px] text-slate-400 font-medium italic">
        *Tips: Pastikan centang "Background Graphics" di pengaturan cetak agar warna muncul
    </p>
</div>
