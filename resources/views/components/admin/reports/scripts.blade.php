<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('reportManager', () => ({
        loading: true,
        detailLoading: false,
        
        availableTags: window.__TAGS_DATA__ || [],
        selectedTagsLabel: 'Pilih Tag',
        filters: {
            start_date: new Date().toISOString().split('T')[0].slice(0, 8) + '01',
            end_date: new Date().toISOString().split('T')[0],
            payment_type: 'all',
            tags: [],
            search: ''
        },
        
        summary: { total_sales: 0, total_profit: 0, total_transactions: 0, avg_transaction: 0 },
        detailData: [],
        pagination: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 },
        
        charts: { salesProfit: null, profitTag: null, trxTrend: null, hourlyPattern: null },
        heatmapData: { heatmap: {}, hours: [], total_transactions: 0, period: {}, max_value: 1, peak_day: null, peak_hour: null },

        init() {
            this.loadData();
            this.loadHourlyPattern();
            
            this.$watch('filters.tags', () => this.updateTagsLabel());
            this.$watch('filters.search', () => this.loadDetail(1));
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
                this.loadHourlyPattern()
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
                if (data.success) this.summary = data.data;
            } catch (e) {
                console.error('Failed to load summary', e);
            }
        },

        async loadCharts() {
            try {
                const res = await fetch(`/api/admin/reports/charts?${this.getFilterParams()}`);
                const data = await res.json();
                if (data.success) this.renderCharts(data.data);
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
            if (this.charts.salesProfit) this.charts.salesProfit.destroy();
            if (this.charts.profitTag) this.charts.profitTag.destroy();
            if (this.charts.trxTrend) this.charts.trxTrend.destroy();

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            };

            const ctxSP = document.getElementById('salesProfitChart').getContext('2d');
            this.charts.salesProfit = new Chart(ctxSP, {
                type: 'line',
                data: {
                    labels: data.sales_profit_trend.map(d => this.formatDateShort(d.date)),
                    datasets: [
                        { label: 'Penjualan', data: data.sales_profit_trend.map(d => d.sales), borderColor: '#4f46e5', backgroundColor: '#4f46e5', tension: 0.3 },
                        { label: 'Laba', data: data.sales_profit_trend.map(d => d.profit), borderColor: '#10b981', backgroundColor: '#10b981', tension: 0.3 }
                    ]
                },
                options: commonOptions
            });

            const ctxPT = document.getElementById('profitTagChart').getContext('2d');
            this.charts.profitTag = new Chart(ctxPT, {
                type: 'doughnut',
                data: {
                    labels: data.profit_by_tag.map(d => d.tag_name),
                    datasets: [{
                        data: data.profit_by_tag.map(d => d.profit),
                        backgroundColor: ['#4f46e5', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ef4444', '#14b8a6', '#f97316', '#6366f1']
                    }]
                },
                options: { ...commonOptions, cutout: '70%' }
            });

            const ctxTR = document.getElementById('trxTrendChart').getContext('2d');
            this.charts.trxTrend = new Chart(ctxTR, {
                type: 'line',
                data: {
                    labels: data.transaction_trend.map(d => this.formatDateShort(d.date)),
                    datasets: [{
                        label: 'Jumlah Transaksi',
                        data: data.transaction_trend.map(d => d.count),
                        borderColor: '#f97316',
                        tension: 0.4,
                        fill: true,
                        backgroundColor: 'rgba(249, 115, 22, 0.1)'
                    }]
                },
                options: commonOptions
            });
        },

        async loadHourlyPattern() {
            try {
                const params = this.getFilterParams();
                const res = await fetch(`/api/admin/heatmap/frequency?${params}`);
                const data = await res.json();
                if (data.success) {
                    this.heatmapData = data.data;
                }
            } catch (e) {
                console.error('Failed to load heatmap data', e);
            }
        },

        getHeatmapColor(val, max) {
            if (val === 0) return 'bg-slate-50 border border-slate-100';
            const percent = val / max;
            if (percent < 0.25) return 'bg-green-100';
            if (percent < 0.5) return 'bg-green-300';
            if (percent < 0.75) return 'bg-green-500 text-white';
            return 'bg-green-700 text-white';
        },

        getHeatmapTooltip(day, hour) {
            const count = this.heatmapData.heatmap?.[day]?.[hour] || 0;
            return `${this.getDayName(day)}, Jam ${hour.toString().padStart(2, '0')}:00 - ${count} Transaksi`;
        },

        getDayName(day) {
            return ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][day];
        },

        renderHourlyChart() {
            const canvas = document.getElementById('hourlyPatternChart');
            if (!canvas) return;

            if (this.charts.hourlyPattern) this.charts.hourlyPattern.destroy();

            const ctx = canvas.getContext('2d');
            const dayColors = [
                'rgba(239, 68, 68, 0.7)',  // Red
                'rgba(59, 130, 246, 0.7)',  // Blue
                'rgba(16, 185, 129, 0.7)',  // Green
                'rgba(245, 158, 11, 0.7)',  // Amber
                'rgba(139, 92, 246, 0.7)',  // Purple
                'rgba(6, 182, 212, 0.7)',   // Cyan
                'rgba(236, 72, 153, 0.7)'   // Pink
            ];

            const datasets = this.hourlyData.series.map((series, index) => ({
                label: series.name,
                data: series.data,
                borderColor: dayColors[index].replace('0.7', '1'),
                backgroundColor: dayColors[index],
                borderWidth: 1.5,
                tension: 0.4,
                pointRadius: 2,
                pointHoverRadius: 5,
                fill: true // Enable filling for stacked area
            }));

            this.charts.hourlyPattern = new Chart(ctx, {
                type: 'line',
                data: { labels: this.hourlyData.hours, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: true, position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 11 } } },
                        tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y + ' transaksi' } }
                    },
                    scales: {
                        y: { 
                            stacked: true, // Enable stacking
                            beginAtZero: true, 
                            ticks: { precision: 0, font: { size: 10 } }, 
                            grid: { color: 'rgba(0, 0, 0, 0.05)' } 
                        },
                        x: { 
                            stacked: true, // Enable stacking
                            ticks: { font: { size: 10 } }, 
                            grid: { display: false } 
                        }
                    }
                }
            });
        },

        exportCSV() {
            window.location.href = `/api/admin/reports/export/csv?${this.getFilterParams()}`;
        },

        printReport() {
            window.print();
        },

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
