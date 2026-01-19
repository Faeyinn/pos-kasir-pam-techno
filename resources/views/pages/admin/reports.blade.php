@extends('layouts.admin')

@section('header', 'Laporan & Analisis')

@section('content')
<div x-data="reportManager" x-init="init" class="min-h-screen">
    {{-- Filter Panel --}}
    <x-admin.report-filter />

    {{-- Export Buttons (Separate, Right-aligned) --}}
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

    {{-- Summary Cards --}}
    <div x-show="!loading" x-transition.opacity>
        <x-admin.report-summary />
        
        {{-- Charts --}}
        <x-admin.report-charts />

        {{-- Hourly Pattern Chart (Line Chart) --}}
        <div class="mt-6">
            <x-admin.report-hourly-chart />
        </div>

        {{-- Detail Table --}}
        <x-admin.report-table />
    </div>

    {{-- Loading State --}}
    <div x-show="loading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent mb-4"></div>
        <p class="text-slate-500">Memuat laporan...</p>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportManager', () => ({
            loading: true,
            detailLoading: false,
            
            // Filters
            availableTags: @json($tags),
            selectedTagsLabel: 'Pilih Tag',
            filters: {
                start_date: new Date().toISOString().split('T')[0].slice(0, 8) + '01', // Start of month
                end_date: new Date().toISOString().split('T')[0], // Today
                payment_type: 'all',
                tags: [],
                search: ''
            },
            
            // Data
            summary: {
                total_sales: 0,
                total_profit: 0,
                total_transactions: 0,
                avg_transaction: 0
            },
            detailData: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                from: 0,
                to: 0,
                total: 0
            },
            
            // Chart Instances
            charts: {
                salesProfit: null,
                profitTag: null,
                trxTrend: null,
                hourlyPattern: null  // New chart reference
            },
            
            // Hourly Pattern Data (was heatmapData)
            hourlyData: {
                series: [],
                hours: [],
                total_transactions: 0,
                period: {}
            },

            init() {
                this.loadData();
                this.loadHourlyPattern();  // Changed from loadHeatmap
                
                this.$watch('filters.tags', () => {
                    this.updateTagsLabel();
                });
                this.$watch('filters.search', () => {
                    this.loadDetail(1);
                });
            },

            updateTagsLabel() {
                if (this.filters.tags.length === 0) {
                    this.selectedTagsLabel = 'Pilih Tag';
                } else if (this.filters.tags.length === 1) {
                    const tag = this.availableTags.find(t => t.id == this.filters.tags[0]);
                    this.selectedTagsLabel = tag ? tag.name : '1 Tag';
                } else {
                    this.selectedTagsLabel = `${this.filters.tags.length} Tag Dipilih`;
                }
            },

            async loadData() {
                this.loading = true;
                await Promise.all([
                    this.loadSummary(),
                    this.loadCharts(),
                    this.loadDetail(1),
                    this.loadHourlyPattern()  // Changed from loadHeatmap
                ]);
                this.loading = false;
                this.$nextTick(() => lucide.createIcons());
            },

            async applyFilters() {
                await this.loadData();
            },

            getFilterParams() {
                const params = new URLSearchParams({
                    start_date: this.filters.start_date,
                    end_date: this.filters.end_date,
                    payment_type: this.filters.payment_type,
                    search: this.filters.search
                });
                if (this.filters.tags.length > 0) {
                    params.append('tags', this.filters.tags.join(','));
                }
                return params;
            },

            async loadSummary() {
                try {
                    const res = await fetch(`/api/admin/reports/summary?${this.getFilterParams()}`);
                    const data = await res.json();
                    if (data.success) {
                        this.summary = data.data;
                    }
                } catch (e) {
                    console.error('Failed to load summary', e);
                }
            },

            async loadCharts() {
                try {
                    const res = await fetch(`/api/admin/reports/charts?${this.getFilterParams()}`);
                    const data = await res.json();
                    if (data.success) {
                        this.renderCharts(data.data);
                    }
                } catch (e) {
                    console.error('Failed to load charts', e);
                }
            },

            async loadDetail(page = 1) {
                this.detailLoading = true;
                try {
                    const params = this.getFilterParams();
                    params.append('page', page);
                    
                    const res = await fetch(`/api/admin/reports/detail?${params}`);
                    const data = await res.json();
                    if (data.success) {
                        this.detailData = data.data.data;
                        this.pagination = {
                            current_page: data.data.current_page,
                            last_page: data.data.last_page,
                            from: data.data.from,
                            to: data.data.to,
                            total: data.data.total
                        };
                    }
                } catch (e) {
                    console.error('Failed to load detail', e);
                } finally {
                    this.detailLoading = false;
                }
            },

            changePage(page) {
                if (page < 1 || page > this.pagination.last_page) return;
                this.loadDetail(page);
            },

            renderCharts(data) {
                // Destroy old charts if match
                if (this.charts.salesProfit) this.charts.salesProfit.destroy();
                if (this.charts.profitTag) this.charts.profitTag.destroy();
                if (this.charts.trxTrend) this.charts.trxTrend.destroy();

                const commonOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                };

                // 1. Sales vs Profit
                const ctxSP = document.getElementById('salesProfitChart').getContext('2d');
                this.charts.salesProfit = new Chart(ctxSP, {
                    type: 'line',
                    data: {
                        labels: data.sales_profit_trend.map(d => this.formatDateShort(d.date)),
                        datasets: [
                            {
                                label: 'Penjualan',
                                data: data.sales_profit_trend.map(d => d.sales),
                                borderColor: '#4f46e5', // Indigo 600
                                backgroundColor: '#4f46e5',
                                tension: 0.3
                            },
                            {
                                label: 'Laba',
                                data: data.sales_profit_trend.map(d => d.profit),
                                borderColor: '#10b981', // Emerald 500
                                backgroundColor: '#10b981',
                                tension: 0.3
                            }
                        ]
                    },
                    options: { ...commonOptions }
                });

                // 2. Profit by Tag (Donut)
                const ctxPT = document.getElementById('profitTagChart').getContext('2d');
                this.charts.profitTag = new Chart(ctxPT, {
                    type: 'doughnut',
                    data: {
                        labels: data.profit_by_tag.map(d => d.tag_name),
                        datasets: [{
                            data: data.profit_by_tag.map(d => d.profit),
                            backgroundColor: [
                                '#4f46e5', '#ec4899', '#f59e0b', '#10b981', '#3b82f6',
                                '#8b5cf6', '#ef4444', '#14b8a6', '#f97316', '#6366f1'
                            ]
                        }]
                    },
                    options: { 
                        ...commonOptions,
                        cutout: '70%'
                    }
                });

                // 3. Transaction Trend
                const ctxTR = document.getElementById('trxTrendChart').getContext('2d');
                this.charts.trxTrend = new Chart(ctxTR, {
                    type: 'line',
                    data: {
                        labels: data.transaction_trend.map(d => this.formatDateShort(d.date)),
                        datasets: [{
                            label: 'Jumlah Transaksi',
                            data: data.transaction_trend.map(d => d.count),
                            borderColor: '#f97316', // Orange 500
                            backgroundColor: '#f97316',
                            tension: 0.4,
                            fill: true,
                            backgroundColor: 'rgba(249, 115, 22, 0.1)'
                        }]
                    },
                    options: { ...commonOptions }
                });
            },

            // Load Heatmap Data
            async loadHeatmap() {
                try {
                    const params = this.getFilterParams();
                    const res = await fetch(`/api/admin/heatmap/frequency?${params}`);
                    const data = await res.json();
                    if (data.success) {
                        this.heatmapData = data.data;
                    }
                } catch (e) {
                    console.error('Failed to load heatmap', e);
                }
            },

            // Load Hourly Pattern Data
            async loadHourlyPattern() {
                try {
                    const params = this.getFilterParams();
                    const res = await fetch(`/api/admin/heatmap/frequency?${params}`);
                    const data = await res.json();
                    if (data.success) {
                        this.hourlyData = data.data;
                        this.$nextTick(() => this.renderHourlyChart());
                    }
                } catch (e) {
                    console.error('Failed to load hourly pattern', e);
                }
            },

            // Render Hourly Pattern Chart
            renderHourlyChart() {
                const canvas = document.getElementById('hourlyPatternChart');
                if (!canvas) return;

                // Destroy existing chart
                if (this.charts.hourlyPattern) {
                    this.charts.hourlyPattern.destroy();
                }

                const ctx = canvas.getContext('2d');
                
                // Day colors (7 different colors for 7 days)
                const dayColors = [
                    '#ef4444', // Red - Minggu
                    '#3b82f6', // Blue - Senin
                    '#10b981', // Green - Selasa
                    '#f59e0b', // Orange - Rabu
                    '#8b5cf6', // Purple - Kamis
                    '#06b6d4', // Cyan - Jumat
                    '#ec4899'  // Pink - Sabtu
                ];

                // Prepare datasets with colors
                const datasets = this.hourlyData.series.map((series, index) => ({
                    label: series.name,
                    data: series.data,
                    borderColor: dayColors[index],
                    backgroundColor: dayColors[index],
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }));

                this.charts.hourlyPattern = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.hourlyData.hours,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: { size: 11 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + ' transaksi';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font: { size: 10 }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    font: { size: 10 }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            },

            exportCSV() {
                const params = this.getFilterParams();
                window.location.href = `/api/admin/reports/export/csv?${params}`;
            },

            printReport() {
                window.print();
            },

            // Formatters
            formatCurrency(val) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
            },
            formatDate(str) {
                return new Date(str).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            },
            formatDateShort(str) {
                return new Date(str).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            }
        }));
    });
</script>

<style>
    @media print {
        /* Hide non-printable elements */
        header, .sidebar, button, input, select, nav, .no-print {
            display: none !important;
        }
        
        /* Reset main container */
        main {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        
        .w-64 { width: 0 !important; }
        .flex-1 { margin-left: 0 !important; }
        
        /* Ensure body uses full page */
        body {
            margin: 0;
            padding: 15mm;
            background: white;
        }
        
        /* Page break controls */
        .page-break-before {
            page-break-before: always;
        }
        
        .page-break-after {
            page-break-after: always;
        }
        
        .avoid-page-break {
            page-break-inside: avoid;
        }
        
        /* Optimize tables for printing */
        table {
            page-break-inside: auto;
            border-collapse: collapse;
            width: 100%;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        thead {
            display: table-header-group; /* Repeat header on each page */
        }
        
        tfoot {
            display: table-footer-group;
        }
        
        /* Ensure charts and cards print well */
        canvas {
            max-width: 100% !important;
            height: auto !important;
        }
        
        /* Optimize card grids for print */
        .grid {
            display: grid !important;
        }
        
        /* Make sure all content is visible */
        * {
            overflow: visible !important;
        }
        
        /* Remove box shadows and transitions for cleaner print */
        * {
            box-shadow: none !important;
            transition: none !important;
            animation: none !important;
        }
        
        /* Ensure proper text rendering */
        body {
            color: #000 !important;
            font-family: Arial, sans-serif;
        }
        
        /* Print-friendly link styles */
        a {
            text-decoration: none;
            color: inherit;
        }
    }
    
    /* Print page size - Portrait A4 */
    @page {
        size: A4 portrait;
        margin: 15mm;
    }
</style>
@endpush
@endsection
