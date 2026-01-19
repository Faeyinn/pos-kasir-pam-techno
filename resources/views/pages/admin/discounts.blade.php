@extends('layouts.admin')

@section('header', 'Manajemen Diskon')

@section('content')
<div x-data="discountManager" x-init="init" class="min-h-screen space-y-6">
    {{-- Header with Add Button --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Diskon</h2>
            <p class="text-sm text-slate-600 mt-1">Kelola diskon produk dan kategori</p>
        </div>
        
        <button 
            @click="openModal('create')"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2 transition-colors"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Diskon
        </button>
    </div>

    {{-- Discount Table --}}
    <x-admin.discount-table />

    {{-- Analytics Section --}}
    <div class="space-y-6">
        <div class="border-t pt-6">
            <h2 class="text-xl font-bold text-slate-900">
                📊 Analisis Efektivitas Diskon
            </h2>
            <p class="text-sm text-slate-600 mt-1">
                Data 30 hari terakhir • Update real-time
            </p>
        </div>

        <x-admin.discount-analytics-comparison />
        <x-admin.discount-analytics-table />
    </div>

    {{-- Discount Modal --}}
    <x-admin.discount-modal :products="$products" :tags="$tags" />
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('discountManager', () => ({
        discounts: @json($discounts),
        products: @json($products),
        tags: @json($tags),
        
        modalMode: 'create',
        showModal: false,
        
        formData: {
            id: null,
            name: '',
            type: 'percentage',
            value: 0,
            target_type: 'product',
            target_ids: [],
            start_date: '',
            end_date: '',
            is_active: false,  // Will be auto-activated based on schedule if auto_activate=true
            auto_activate: true  // Default to auto-activation
        },

        // Search states for modal
        productSearch: '',
        tagSearch: '',

        // Analytics data
        comparison: {
            without_discount: {},
            with_discount: {},
            diff: {}
        },
        performance: [],

        init() {
            this.$nextTick(() => {
                lucide.createIcons();
            });
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
            
            // Reset search fields
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
                    auto_activate: discount.auto_activate ?? true  // Default to true if not set
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
                id: null,
                name: '',
                type: 'percentage',
                value: 0,
                target_type: 'product',
                target_ids: [],
                start_date: '',
                end_date: '',
                is_active: false,
                auto_activate: true  // Default to auto-activation
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
            // Confirmation dialog
            const statusText = currentStatus ? 'menonaktifkan' : 'mengaktifkan';
            const confirmed = confirm(`Apakah Anda yakin ingin ${statusText} diskon ini?`);
            
            if (!confirmed) {
                return; // Cancel if user clicks "Cancel"
            }

            try {
                const res = await fetch(`/api/admin/discounts/${discountId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await res.json();
                if (data.success) {
                    location.reload();
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        },

        async deleteDiscount(id) {
            if (!confirm('Anda yakin ingin menghapus diskon ini?')) return;
            
            try {
                const res = await fetch(`/api/admin/discounts/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await res.json();
                alert(data.message);
                
                if (data.success) {
                    location.reload();
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        },

        getTargetNames(discount) {
            if (discount.target_type === 'product') {
                return discount.products.map(p => p.name).join(', ');
            } else {
                return discount.tags.map(t => t.name).join(', ');
            }
        },

        formatValue(discount) {
            if (discount.type === 'percentage') {
                return discount.value + '%';
            } else {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(discount.value);
            }
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
            } else {
                return `⚠️ Profit turun ${Math.abs(profitDiff)}% dengan diskon. 
                        Pertimbangkan untuk mengurangi nilai diskon atau meningkatkan minimum purchase.`;
            }
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
            // Get all product IDs from the products array
            this.formData.target_ids = this.products.map(p => p.id);
        },

        selectAllTags() {
            // Get all tag IDs from the tags array
            this.formData.target_ids = this.tags.map(t => t.id);
        }
    }));
});
</script>
@endpush
