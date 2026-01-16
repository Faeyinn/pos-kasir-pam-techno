<div
    x-show="showScannerModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/90 backdrop-blur-sm"
    x-transition.opacity
    x-cloak
>
    <!-- Scanner Container -->
    <div 
        class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md relative overflow-hidden mx-4"
        @click.outside="stopScanner()"
    >
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Scan QR/Barcode</h3>
            <button @click="stopScanner()" class="p-2 bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                <i data-lucide="x" class="w-5 h-5 text-gray-600"></i>
            </button>
        </div>

        <div class="relative w-full aspect-square bg-black rounded-xl overflow-hidden">
            <div id="reader" class="w-full h-full"></div>
            
            <!-- Overlay Guide -->
            <div class="absolute inset-0 border-2 border-white/30 pointer-events-none flex items-center justify-center">
                <div class="w-48 h-48 border-2 border-blue-500 rounded-lg relative">
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-4 border-l-4 border-blue-500 -mt-1 -ml-1"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-4 border-r-4 border-blue-500 -mt-1 -mr-1"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-4 border-l-4 border-blue-500 -mb-1 -ml-1"></div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-4 border-r-4 border-blue-500 -mb-1 -mr-1"></div>
                </div>
            </div>
        </div>

        <p class="text-center text-sm text-gray-500 mt-4">Arahkan kamera ke kode produk</p>
    </div>
</div>
