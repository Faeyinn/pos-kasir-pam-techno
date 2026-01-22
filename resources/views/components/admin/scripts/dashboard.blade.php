    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function adminDashboard() {
    // Keep chart instances outside of Alpine reactive scope to prevent proxy issues
    let salesChartInstance = null;
    let categoryChartInstance = null;

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
        recentTransactions: [],
        period: 'monthly',
        // Removed chart properties from reactive object
        
        // Internal state
        _listenersAdded: false,
        _lastFetch: 0,

        apiGet(url) {
            return fetch(url, {
                method: 'GET',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        },

        async init() {
            await this.fetchAllData();

            // Prevent adding multiple listeners
            if (!this._listenersAdded) {
                this._listenersAdded = true;
                this._lastFetch = Date.now();
                
                // Refresh data when returning to the tab/window (with minimum 5 minute interval)
                const refreshIfStale = () => {
                    const now = Date.now();
                    // 5 minutes (300,000 ms) interval to prevent annoying skeleton loads
                    if (now - this._lastFetch > 300000) {
                        this._lastFetch = now;
                        this.fetchAllData(true); // true = background refresh (no skeleton)
                    }
                };
                
                window.addEventListener('focus', refreshIfStale);
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') {
                        refreshIfStale();
                    }
                });
            }

            this.$nextTick(() => {
                lucide.createIcons();
            });
        },

        async fetchAllData(isBackgroundRefresh = false) {
            if (!isBackgroundRefresh) {
                this.loading = true;
            }
            try {
                await Promise.all([
                    this.fetchStats(),
                    this.fetchTrend(),
                    this.fetchCategorySales(),
                    this.fetchTopProducts(),
                    this.fetchRecentTransactions()
                ]);
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            } finally {
                this.loading = false;
                // Use setTimeout to ensure DOM is fully updated after x-show changes
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.initCharts();
                        lucide.createIcons();
                    }, 50);
                });
            }
        },

        async fetchStats() {
            try {
                const response = await this.apiGet('/api/admin/stats');
                const data = await response.json();
                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Error fetching stats:', error);
            }
        },

        async fetchTrend() {
            try {
                const response = await this.apiGet('/api/admin/sales-profit-trend');
                const data = await response.json();
                if (data.success) {
                    this.trendData = data.data;
                }
            } catch (error) {
                console.error('Error fetching trend:', error);
            }
        },

        async fetchCategorySales() {
            try {
                const response = await this.apiGet('/api/admin/category-sales');
                const data = await response.json();
                if (data.success) {
                    this.categoryData = data.data;
                }
            } catch (error) {
                console.error('Error fetching category sales:', error);
            }
        },

        async fetchTopProducts() {
            try {
                const response = await this.apiGet(`/api/admin/top-products?period=${encodeURIComponent(this.period)}`);
                const data = await response.json();
                if (data.success) {
                    this.topProducts = data.data;
                }
            } catch (error) {
                console.error('Error fetching top products:', error);
            }
        },

        async fetchRecentTransactions() {
            try {
                const response = await this.apiGet('/api/admin/recent-transactions');
                const data = await response.json();
                if (data.success) {
                    this.recentTransactions = data.data;
                }
            } catch (error) {
                console.error('Error fetching recent transactions:', error);
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
            try {
                this.initSalesProfitChart();
                if (!this.categoryData.empty) {
                    this.initCategoryChart();
                }
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        },

        initSalesProfitChart() {
            const canvas = document.getElementById('salesProfitChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            if (salesChartInstance) {
                salesChartInstance.destroy();
                salesChartInstance = null;
            }

            // Ensure we have data
            if (!this.trendData || !Array.isArray(this.trendData)) return;

            salesChartInstance = new Chart(canvas, {
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
            const canvas = document.getElementById('categoryChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            if (categoryChartInstance) {
                categoryChartInstance.destroy();
                categoryChartInstance = null;
            }

            // Ensure we have data
            if (!this.categoryData || !this.categoryData.labels) return;

            categoryChartInstance = new Chart(canvas, {
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
                                    const percentage = this.categoryData.total > 0 
                                        ? ((context.parsed / this.categoryData.total) * 100).toFixed(1)
                                        : '0';
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
