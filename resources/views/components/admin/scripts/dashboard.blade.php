    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function adminDashboard() {
    return {
        loading: true,
        stats: {
            sales_today: 0,
            profit_today: 0,
            transactions_today: 0,
            low_stock_count: 0
        },
        trendData: [],
        categoryData: {
            labels: [],
            values: [],
            empty: true
        },
        topProducts: [],
        period: 'monthly',  // Changed from 'daily' to show seeded data
        salesProfitChart: null,
        categoryChart: null,

        async init() {
            await this.fetchAllData();
            this.$nextTick(() => {
                lucide.createIcons();
            });
        },

        async fetchAllData() {
            this.loading = true;
            try {
                await Promise.all([
                    this.fetchStats(),
                    this.fetchTrend(),
                    this.fetchCategorySales(),
                    this.fetchTopProducts()
                ]);
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    this.initCharts();
                    lucide.createIcons();
                });
            }
        },

        async fetchStats() {
            try {
                const response = await fetch('/api/admin/stats');
                const data = await response.json();
                console.log('Stats data:', data);
                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Error fetching stats:', error);
            }
        },

        async fetchTrend() {
            try {
                const response = await fetch('/api/admin/sales-profit-trend');
                const data = await response.json();
                console.log('Trend data:', data);
                if (data.success) {
                    this.trendData = data.data;
                }
            } catch (error) {
                console.error('Error fetching trend:', error);
            }
        },

        async fetchCategorySales() {
            try {
                const response = await fetch('/api/admin/category-sales');
                const data = await response.json();
                console.log('Category data:', data);
                if (data.success) {
                    this.categoryData = data.data;
                }
            } catch (error) {
                console.error('Error fetching category sales:', error);
            }
        },

        async fetchTopProducts() {
            try {
                const response = await fetch(`/api/admin/top-products?period=${this.period}`);
                const data = await response.json();
                console.log('Top products data:', data);
                if (data.success) {
                    this.topProducts = data.data;
                }
            } catch (error) {
                console.error('Error fetching top products:', error);
            }
        },

        async changePeriod(newPeriod) {
            this.period = newPeriod;
            this.loading = true;
            await this.fetchTopProducts();
            this.loading = false;
            this.$nextTick(() => lucide.createIcons());
        },

        getPeriodLabel() {
            const labels = {
                'daily': 'Hari Ini',
                'weekly': '7 Hari',
                'monthly': '30 Hari'
            };
            return 'Periode: ' + labels[this.period];
        },

        initCharts() {
            this.initSalesProfitChart();
            if (!this.categoryData.empty) {
                this.initCategoryChart();
            }
        },

        initSalesProfitChart() {
            const ctx = document.getElementById('salesProfitChart');
            if (!ctx) return;

            if (this.salesProfitChart) {
                this.salesProfitChart.destroy();
            }

            this.salesProfitChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.trendData.map(d => d.label),
                    datasets: [
                        {
                            label: 'Penjualan',
                            data: this.trendData.map(d => d.sales),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Laba',
                            data: this.trendData.map(d => d.profit),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    return context.dataset.label + ': ' + this.formatRupiah(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => {
                                    return 'Rp ' + (value / 1000) + 'k';
                                }
                            }
                        }
                    }
                }
            });
        },

        initCategoryChart() {
            const ctx = document.getElementById('categoryChart');
            if (!ctx) return;

            if (this.categoryChart) {
                this.categoryChart.destroy();
            }

            this.categoryChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: this.categoryData.labels,
                    datasets: [{
                        data: this.categoryData.values,
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(147, 51, 234)',
                            'rgb(245, 158, 11)',
                            'rgb(239, 68, 68)',
                            'rgb(107, 114, 128)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.label || '';
                                    const value = this.formatRupiah(context.parsed);
                                    const percentage = ((context.parsed / this.categoryData.total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }
    }
}
</script>
