<div class="flex justify-end gap-3 mb-6">
    <button 
        @click="exportCSV"
        class="px-4 py-2 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm"
    >
        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
        Excel
    </button>
    <button 
        @click="printReport"
        class="px-4 py-2 bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm"
    >
        <i data-lucide="printer" class="w-4 h-4"></i>
        PDF
    </button>
</div>
