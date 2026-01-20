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
        hourlyData: { series: [], hours: [], total_transactions: 0, period: {} },

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
                    this.hourlyData = data.data;
                    this.$nextTick(() => this.renderHourlyChart());
                }
            } catch (e) {
                console.error('Failed to load hourly pattern', e);
            }
        },

        renderHourlyChart() {
            const canvas = document.getElementById('hourlyPatternChart');
            if (!canvas) return;

            if (this.charts.hourlyPattern) this.charts.hourlyPattern.destroy();

            const ctx = canvas.getContext('2d');
            const dayColors = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899'];

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
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } }
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
