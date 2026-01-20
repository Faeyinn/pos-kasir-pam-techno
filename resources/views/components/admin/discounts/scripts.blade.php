<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('discountManager', () => ({
        discounts: window.__DISCOUNTS_DATA__ || [],
        products: window.__PRODUCTS_DATA__ || [],
        tags: window.__TAGS_DATA__ || [],
        
        modalMode: 'create',
        showModal: false,
        
        formData: {
            id: null, name: '', type: 'percentage', value: 0,
            target_type: 'product', target_ids: [],
            start_date: '', end_date: '', is_active: false, auto_activate: true
        },

        productSearch: '',
        tagSearch: '',

        comparison: { without_discount: {}, with_discount: {}, diff: {} },
        performance: [],

        init() {
            this.$nextTick(() => lucide.createIcons());
            this.loadAnalytics();
        },

        async loadAnalytics() {
            try {
                const res = await fetch('/api/admin/discounts/analytics');
                const data = await res.json();
                
                if (data.success) {
                    this.comparison = data.data.comparison;
                    this.performance = data.data.performance;
                }
            } catch (e) {
                console.error('Failed to load analytics:', e);
            }
        },

        openModal(mode, discount = null) {
            this.modalMode = mode;
            this.productSearch = '';
            this.tagSearch = '';
            
            if (mode === 'edit' && discount) {
                this.formData = {
                    id: discount.id,
                    name: discount.name,
                    type: discount.type,
                    value: discount.value,
                    target_type: discount.target_type,
                    target_ids: discount.target_type === 'product' 
                        ? discount.products.map(p => p.id)
                        : discount.tags.map(t => t.id),
                    start_date: this.formatForInput(discount.start_date),
                    end_date: this.formatForInput(discount.end_date),
                    is_active: discount.is_active,
                    auto_activate: discount.auto_activate ?? true
                };
            } else {
                this.resetForm();
            }
            
            this.showModal = true;
            this.$nextTick(() => lucide.createIcons());
        },

        closeModal() {
            this.showModal = false;
            this.resetForm();
        },

        resetForm() {
            this.formData = {
                id: null, name: '', type: 'percentage', value: 0,
                target_type: 'product', target_ids: [],
                start_date: '', end_date: '', is_active: false, auto_activate: true
            };
        },

        async saveDiscount() {
            const url = this.modalMode === 'create' 
                ? '/api/admin/discounts'
                : `/api/admin/discounts/${this.formData.id}`;
            
            const method = this.modalMode === 'create' ? 'POST' : 'PUT';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await res.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        },

        async toggleStatus(discountId, currentStatus) {
            const statusText = currentStatus ? 'menonaktifkan' : 'mengaktifkan';
            if (!confirm(`Apakah Anda yakin ingin ${statusText} diskon ini?`)) return;

            try {
                const res = await fetch(`/api/admin/discounts/${discountId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await res.json();
                if (data.success) location.reload();
            } catch (e) {
                alert('Error: ' + e.message);
            }
        },

        async deleteDiscount(id) {
            if (!confirm('Anda yakin ingin menghapus diskon ini?')) return;
            
            try {
                const res = await fetch(`/api/admin/discounts/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });

                const data = await res.json();
                alert(data.message);
                if (data.success) location.reload();
            } catch (e) {
                alert('Error: ' + e.message);
            }
        },

        getTargetNames(discount) {
            if (discount.target_type === 'product') {
                return discount.products.map(p => p.name).join(', ');
            }
            return discount.tags.map(t => t.name).join(', ');
        },

        formatValue(discount) {
            if (discount.type === 'percentage') return discount.value + '%';
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(discount.value);
        },

        formatForInput(dateTimeString) {
            if (!dateTimeString) return '';
            const date = new Date(dateTimeString);
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        },

        formatDateTime(dateTimeString) {
            if (!dateTimeString) return '-';
            const date = new Date(dateTimeString);
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },

        getConclusion() {
            const profitDiff = this.comparison.diff?.total_profit || 0;
            const marginDiff = 
                (this.comparison.with_discount?.profit_margin || 0) - 
                (this.comparison.without_discount?.profit_margin || 0);
            
            if (profitDiff > 50) {
                return `Meskipun margin turun ${Math.abs(marginDiff).toFixed(1)}%, 
                        profit meningkat ${profitDiff}% karena volume transaksi meningkat signifikan. 
                        <strong class="text-green-700">✅ Diskon EFEKTIF meningkatkan laba</strong>`;
            } else if (profitDiff > 0) {
                return `Profit meningkat ${profitDiff}% dengan diskon. 
                        <strong class="text-green-700">✅ Diskon menguntungkan</strong>`;
            }
            return `⚠️ Profit turun ${Math.abs(profitDiff)}% dengan diskon. 
                    Pertimbangkan untuk mengurangi nilai diskon atau meningkatkan minimum purchase.`;
        },

        getROIIcon(roi) {
            if (roi > 500) return '🟢';
            if (roi > 200) return '🟡';
            return '🔴';
        },

        getROIColorClass(roi) {
            if (roi > 500) return 'text-green-600';
            if (roi > 200) return 'text-yellow-600';
            return 'text-red-600';
        },

        selectAllProducts() {
            this.formData.target_ids = this.products.map(p => p.id);
        },

        selectAllTags() {
            this.formData.target_ids = this.tags.map(t => t.id);
        }
    }));
});
</script>
