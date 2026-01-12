<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pam Techno POS')</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        [x-cloak] { display: none !important; }
        
        /* Print Styles - Professional Thermal Receipt */
        @media print {
            /* Reset page margins for thermal printer */
            @page {
                margin: 10mm;
                size: 80mm auto;
            }
            
            /* Reset body for printing */
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Hide everything except receipt modal */
            body > * {
                display: none !important;
            }
            
            /* Show only the receipt modal */
            #receipt-modal {
                display: block !important;
                position: static !important;
                background: white !important;
                backdrop-filter: none !important;
                padding: 0 !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                z-index: 1 !important;
            }
            
            /* Show all children of receipt modal */
            #receipt-modal * {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* Hide print-hide elements */
            .print-hide,
            .print-hide * {
                display: none !important;
            }
            
            /* Receipt container styling */
            #receipt-modal > div {
                box-shadow: none !important;
                max-width: 80mm !important;
                width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
                border-radius: 0 !important;
                border: none !important;
                background: white !important;
            }
            
            /* Receipt content area */
            .p-4.bg-gray-50 {
                padding: 8px !important;
                background: white !important;
                max-height: none !important;
                overflow: visible !important;
            }
            
            /* Inner receipt card */
            .bg-white.rounded-xl {
                padding: 8px !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: white !important;
            }
            
            /* Remove all backgrounds */
            .bg-gray-50,
            .bg-gray-100,
            .bg-blue-100,
            .bg-purple-100 {
                background: white !important;
            }
            
            /* Remove all shadows */
            .shadow-sm,
            .shadow-lg,
            .shadow-xl,
            .shadow-2xl {
                box-shadow: none !important;
            }
            
            /* Remove decorative borders */
            .border-gray-100,
            .border-gray-200 {
                border-color: transparent !important;
            }
            
            /* Thermal receipt typography */
            body *,
            #receipt-modal * {
                font-family: 'Courier New', monospace !important;
                color: #000 !important;
                line-height: 1.3 !important;
            }
            
            /* Store name - bold and centered */
            h3 {
                font-size: 14pt !important;
                font-weight: bold !important;
                text-align: center !important;
                margin: 2px 0 !important;
                letter-spacing: 0.5px !important;
            }
            
            /* Paragraph text sizes */
            p {
                margin: 2px 0 !important;
            }
            
            .text-\[10px\] {
                font-size: 8pt !important;
            }
            
            .text-xs {
                font-size: 8pt !important;
            }
            
            .text-sm {
                font-size: 9pt !important;
            }
            
            .text-base {
                font-size: 10pt !important;
            }
            
            /* Remove rounded badges */
            .rounded-full {
                border-radius: 0 !important;
                padding: 0 !important;
                background: transparent !important;
            }
            
            /* Grand total emphasis */
            .text-xl {
                font-size: 12pt !important;
            }
            
            /* Spacing adjustments */
            .space-y-1 > * + * {
                margin-top: 2px !important;
            }
            
            .space-y-2 > * + * {
                margin-top: 4px !important;
            }
            
            .space-y-4 > * + * {
                margin-top: 6px !important;
            }
            
            /* Padding adjustments */
            .p-4 {
                padding: 4px !important;
            }
            
            .p-6 {
                padding: 4px !important;
            }
            
            .pb-3, .pt-3 {
                padding-bottom: 4px !important;
                padding-top: 4px !important;
            }
            
            .pb-2, .pt-2 {
                padding-bottom: 2px !important;
                padding-top: 2px !important;
            }
            
            .pt-1 {
                padding-top: 2px !important;
            }
            
            /* Item list */
            .overflow-y-auto {
                overflow: visible !important;
                max-height: none !important;
            }
            
            .max-h-40 {
                max-height: none !important;
            }
            
            /* Text alignment */
            .text-center {
                text-align: center !important;
            }
            
            /* Flex to block for printing */
            .flex {
                display: block !important;
            }
            
            .flex-1 {
                flex: none !important;
            }
            
            /* Justify between to block */
            .justify-between {
                display: flex !important;
                justify-content: space-between !important;
            }
            
            .items-center,
            .items-start {
                display: flex !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div 
        class="flex h-screen bg-gray-50 overflow-hidden" 
        x-data="{ 
            sidebarOpen: false,
            paymentType: 'retail'
        }"
    >
        <!-- Sidebar -->
        <x-sidebar />

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <x-header />

            <!-- Main Content -->
            <main class="flex-1 overflow-hidden">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
