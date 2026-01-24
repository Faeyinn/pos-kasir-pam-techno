<style>
    @media print {
        header, .sidebar, button, input, select, nav, .no-print {
            display: none !important;
        }
        
        main {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        
        .w-64 { width: 0 !important; }
        .flex-1 { margin-left: 0 !important; }
        
        body {
            margin: 0;
            padding: 10mm;
            background: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .page-break-before { page-break-before: always; }
        .page-break-after { page-break-after: always; }
        .avoid-page-break { page-break-inside: avoid; }
        
        table {
            page-break-inside: auto;
            border-collapse: collapse;
            width: 100%;
        }
        
        tr { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        
        canvas {
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
            margin: 0 auto !important;
        }
        
        .grid { display: grid !important; }
        
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            overflow: visible !important;
            box-shadow: none !important;
            transition: none !important;
            animation: none !important;
        }
        
        body {
            color: #000 !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        a { text-decoration: none; color: inherit; }
    }
    
    @page {
        size: A4 portrait;
        margin: 10mm;
    }
</style>
