<style>
    @media print {
        /* 1. Reset Global Layout & Hide UI Elements */
        header, .sidebar, button, input, select, nav, .no-print, [role="button"], form, .loading-overlay {
            display: none !important;
        }
        
        /* 2. Force Layout to be Static and Visible */
        html, body {
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
            background: white !important;
            position: static !important;
        }

        /* Reset structural containers only - avoided broad attribute selectors that break components */
        .h-screen, .min-h-screen, main, .overflow-hidden, .overflow-auto,
        .h-72, .h-64, .h-96, .h-40 {
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            overflow: visible !important;
            position: static !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* 3. Spacing & Page Boundaries */
        body {
            padding: 5mm !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color: #000 !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* Reduce vertical spacing for print to save space */
        [class*="space-y-"] > * + * {
            margin-top: 1rem !important;
        }

        .page-break-before { page-break-before: always; }
        .page-break-after { page-break-after: always; }
        
        /* Protect smaller elements from being split */
        .avoid-page-break, .summary-card, .chart-card, .card {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            display: block !important;
            position: relative !important;
        }

        /* 4. Layout Alignment */
        
        /* Headers centering */
        .text-center, h1, h2, h3, h4 {
            text-align: center !important;
        }
        
        /* Ensure containers can break if needed, but units stay whole */
        section, .bg-white {
            page-break-inside: auto !important; 
        }

        /* 5. Component Specific Adjustments - Targeted Grid Overrides */
        
        /* Summary Grid on Reports Page */
        .xl\:grid-cols-4.grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 15px !important;
        }

        /* Comparison Grid on Discount Page */
        .lg\:grid-cols-2.grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 20px !important;
        }

        /* Charts Grid */
        .grid-cols-1.lg\:grid-cols-2.gap-6 {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 20px !important;
        }

        .lg\:col-span-2 {
            grid-column: span 2 !important;
        }

        table {
            page-break-inside: auto;
            border-collapse: collapse;
            width: 100% !important;
            margin: 0 auto !important;
        }
        
        tr { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; }
        
        canvas {
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
            margin: 1rem auto !important;
        }
        
        /* Heatmap Fixes */
        .heatmap-container {
            min-width: 0 !important;
            width: 100% !important;
        }

        .heatmap-cell-container, 
        .heatmap-x-labels,
        .flex.items-center.group\/row, 
        .flex-1.flex.gap-1 {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            flex-wrap: nowrap !important;
        }
        
        .heatmap-cell {
            aspect-ratio: 1/1 !important;
            min-width: 10px !important;
            min-height: 10px !important;
            flex: 1 1 auto !important;
        }

        /* 6. General Cleanup */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            box-shadow: none !important;
            transition: none !important;
            animation: none !important;
        }
        
        .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl {
            box-shadow: none !important;
        }
    }
    
    @page {
        size: A4 portrait;
        margin: 5mm;
    }
</style>
