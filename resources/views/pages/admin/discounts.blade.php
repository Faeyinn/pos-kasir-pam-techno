@extends('layouts.admin')

@section('header', 'Manajemen Diskon')

@section('content')
<div x-data="discountManager" x-init="init" class="min-h-screen">
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
            is_active: true
        },

        init() {
            // Initial icon creation after Alpine renders
            this.$nextTick(() => {
                lucide.createIcons();
            });
        },

        openModal(mode, discount = null) {
            this.modalMode = mode;
            
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
                    is_active: discount.is_active
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
                is_active: true
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

        async toggleStatus(id) {
            try {
                const res = await fetch(`/api/admin/discounts/${id}/toggle`, {
                    method: 'POST',
                    headers: {
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

        /**
         * Format MySQL datetime to datetime-local input format
         * Converts: "2026-01-18 14:30:00" -> "2026-01-18T14:30"
         */
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

        /**
         * Format datetime for display in table
         * Converts to: "DD/MM/YYYY HH:MM"
         */
        formatDateTime(dateTimeString) {
            if (!dateTimeString) return '-';
            
            const date = new Date(dateTimeString);
            
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }
    }));
});
</script>
@endpush
