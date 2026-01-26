<style>
    @media print {
        /* 1. Base Structural Resets */
        html, body {
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            background: white !important;
            position: static !important;
        }

        .h-screen, .min-h-screen, main, .overflow-hidden, .overflow-auto,
        .h-72, .h-64, .h-96, .h-40 {
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            position: static !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body {
            padding: 10mm !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color: #000 !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* 2. Alignment & Page Breaks */
        .page-break-before { page-break-before: always; }
        .page-break-after { page-break-after: always; }
        
        .avoid-page-break, section, .card, .chart-container, .bg-white.print-visible {
            page-break-inside: avoid !important;
            display: block !important;
            margin-left: auto !important;
            margin-right: auto !important;
            float: none !important;
        }

        /* We specifically target charts and summary to be centered */
        .bg-white p, .bg-white h4, .bg-white .text-center {
            text-align: center !important;
        }

        /* 3. Component Specifics */
        .grid {
            display: block !important;
        }

        .grid > div {
            width: 100% !important;
            max-width: 100% !important;
            margin-bottom: 2rem !important;
        }

        canvas {
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
            margin: 0 auto 2rem auto !important;
        }
        
        /* Heatmap Preserve Flex */
        .heatmap-cell-container, 
        .heatmap-x-labels,
        .flex.items-center.group\/row, 
        .flex-1.flex.gap-1 {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
        }
        
        .heatmap-cell {
            aspect-ratio: 1/1 !important;
            min-width: 15px !important;
            min-height: 15px !important;
        }

        /* 4. ULTIMATE HIDING RULE (Must be at the bottom / most specific) */
        header, .sidebar, button, input, select, nav, [role="button"], form, .loading-overlay, .no-print {
            display: none !important;
            height: 0 !important;
            width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            box-shadow: none !important;
            transition: none !important;
            animation: none !important;
        }
    }
    
    @page {
        size: A4 portrait;
        margin: 10mm;
    }
</style>
