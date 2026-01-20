<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pam Techno POS')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased selection:bg-blue-500 selection:text-white">
    <div class="fixed inset-0 -z-10 overflow-hidden bg-slate-50/50">
        <div class="absolute top-[-10%] left-[-5%] w-96 h-96 bg-blue-400 rounded-full blur-[100px] opacity-20"></div>
        <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-purple-300 rounded-full blur-[100px] opacity-30"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-[600px] h-[600px] bg-indigo-200 rounded-full blur-[120px] opacity-40"></div>
    </div>

    <div 
        class="flex h-screen bg-transparent overflow-hidden relative z-10" 
        x-data="{ 
            sidebarOpen: false,
            paymentType: 'retail',
            showScannerModal: false, 
            scanner: null,

            openScanner() {
                this.showScannerModal = true;
                this.$nextTick(() => this.startScanner());
            },

            startScanner() {
                if (this.scanner || !window.Html5Qrcode) return;

                this.scanner = new Html5Qrcode('reader');
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                this.scanner.start(
                    { facingMode: 'environment' },
                    config,
                    (decodedText) => {
                        const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-software-interface-start-2574.mp3'); 
                        audio.play().catch(() => {});
                        this.stopScanner();
                        this.$dispatch('scan-success', decodedText);
                        window.lucide && lucide.createIcons();
                    },
                    () => {}
                ).catch(err => {
                    console.error('Error starting scanner', err);
                    alert('Gagal mengakses kamera. Mohon izinkan akses kamera.');
                    this.showScannerModal = false;
                });
            },

            stopScanner() {
                this.showScannerModal = false;
                if (this.scanner) {
                    this.scanner.stop().then(() => {
                        this.scanner.clear();
                        this.scanner = null;
                    }).catch(() => {
                        this.scanner = null;
                    });
                }
            }
        }"
    >
        <div class="flex-1 flex flex-col min-w-0">
            <x-header />
            <main class="flex-1 overflow-hidden">
                @yield('content')
            </main>
        </div>

        <x-kasir.modals.scanner />
    </div>

    @stack('scripts')

</body>
</html>
