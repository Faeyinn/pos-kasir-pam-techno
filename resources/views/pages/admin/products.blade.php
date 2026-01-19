@extends('layouts.admin')

@section('header', 'Manajemen Produk')

@section('content')
<div x-data="productManager" x-init="init">
    {{-- Header Actions --}}
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Produk</h2>
            <p class="text-sm text-slate-600 mt-1 mb-4">Kelola produk dan harga untuk sistem POS</p>
            
            {{-- Search Bar --}}
            <div class="relative group ">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 w-4 h-4 transition-colors"></i>
                <input 
                    type="text" 
                    x-model="search"
                    class="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-slate-700 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 placeholder:text-slate-400 w-64 transition-all shadow-sm"
                    placeholder="Cari produk..."
                >
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            {{-- Stats Summary --}}
            <div class="flex items-center gap-4 px-4 py-2 bg-white rounded-xl border border-slate-200">
                <div class="text-center">
                    <div class="text-xs text-slate-500">Total Produk</div>
                    <div class="text-lg font-bold text-slate-900" x-text="products.length"></div>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div class="text-center">
                    <div class="text-xs text-slate-500">Aktif</div>
                    <div class="text-lg font-bold text-green-600" x-text="products.filter(p => p.is_active).length"></div>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div class="text-center">
                    <div class="text-xs text-slate-500">Stok Rendah</div>
                    <div class="text-lg font-bold text-red-600" x-text="products.filter(p => p.stock < 20).length"></div>
                </div>
            </div>

            {{-- Tambah Produk Button --}}
            <button
                type="button"
                x-on:click="openAddModal"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors shadow-lg shadow-indigo-600/30 text-sm"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Produk</span>
            </button>
        </div>
    </div>

    {{-- Product Table --}}
    <x-admin.product-table />

    {{-- Product Add Modal --}}
    <x-admin.product-add-modal />

    {{-- Product Edit Modal --}}
    <x-admin.product-edit-modal />

    {{-- Toast Notification --}}
    <div 
        x-show="toast.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-50"
        style="display: none;"
    >
        <div 
            class="px-6 py-4 rounded-xl shadow-lg border flex items-center gap-3 min-w-[300px]"
            :class="{
                'bg-green-50 border-green-200': toast.type === 'success',
                'bg-red-50 border-red-200': toast.type === 'error',
                'bg-blue-50 border-blue-200': toast.type === 'info'
            }"
        >
            <i 
                :data-lucide="toast.type === 'success' ? 'check-circle' : toast.type === 'error' ? 'x-circle' : 'info'"
                class="w-5 h-5"
                :class="{
                    'text-green-600': toast.type === 'success',
                    'text-red-600': toast.type === 'error',
                    'text-blue-600': toast.type === 'info'
                }"
            ></i>
            <span 
                class="font-medium"
                :class="{
                    'text-green-900': toast.type === 'success',
                    'text-red-900': toast.type === 'error',
                    'text-blue-900': toast.type === 'info'
                }"
                x-text="toast.message"
            ></span>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productManager', () => ({
        products: @json($products),
        availableTags: @json($tags),
        search: '',
        
        get filteredProducts() {
            if (!this.search) return this.products;
            const q = this.search.toLowerCase();
            return this.products.filter(p => 
                p.name.toLowerCase().includes(q) ||
                (p.tags && p.tags.some(t => t.name.toLowerCase().includes(q)))
            );
        },

        // Edit Product State
        form: {
            id: null,
            name: '',
            price: 0,
            cost_price: 0,
            wholesale: 0,
            wholesale_unit: '',
            wholesale_qty_per_unit: 1,
            stock: 0,
            is_active: true,
            tag_ids: []
        },
        errors: {},
        loading: false,
        
        // Add Product State
        addForm: {
            name: '',
            price: 0,
            cost_price: 0,
            wholesale: 0,
            wholesale_unit: '',
            wholesale_qty_per_unit: 1,
            stock: 0,
            is_active: true,
            tag_ids: []
        },
        addErrors: {},
        addLoading: false,
        
        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        // Helper to toggle tag ID in a target array (by string path)
        toggleTagList(arrayPath, tagId) {
            // Validate input
            if (!tagId) return;

            // Resolve the array from the path string (e.g., "addForm.tag_ids")
            let parts = arrayPath.split('.');
            let target = this;
            
            // Traverse to the parent object
            for (let i = 0; i < parts.length - 1; i++) {
                if (target[parts[i]]) {
                    target = target[parts[i]];
                } else {
                    console.error('Invalid path:', arrayPath);
                    return;
                }
            }
            
            // Get the final array key
            let key = parts[parts.length - 1];
            
            // Ensure array exists
            if (!Array.isArray(target[key])) {
                target[key] = [];
            }
            
            // Toggle logic
            if (target[key].includes(tagId)) {
                target[key] = target[key].filter(id => id !== tagId);
            } else {
                target[key].push(tagId);
            }
        },

        // Helper to find tag object
        getTag(id) {
            return this.availableTags.find(t => t.id === id);
        },

        init() {
            this.$nextTick(() => {
                lucide.createIcons();
            });
        },

        openAddModal() {
            // Reset form
            this.addForm = {
                name: '',
                price: 0,
                cost_price: 0,
                wholesale: 0,
                wholesale_unit: '',
                wholesale_qty_per_unit: 1,
                stock: 0,
                is_active: true,
                tags: []
            };
            this.addTagsInput = '';
            this.addErrors = {};
            
            this.$dispatch('open-product-add');
        },

        async createProduct() {
            this.addLoading = true;
            this.addErrors = {};

            try {
                const response = await fetch('/api/admin/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.addForm)
                });

                const data = await response.json();

                if (data.success) {
                    // Add new product to list
                    this.products.push(data.data);

                    this.$dispatch('close-product-add');
                    this.showToast('Produk berhasil ditambahkan', 'success');
                    
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                } else {
                    this.addErrors = data.errors || {};
                    this.showToast(data.message || 'Gagal menambahkan produk', 'error');
                }
            } catch (error) {
                console.error('Error creating product:', error);
                this.showToast('Terjadi kesalahan saat menyimpan', 'error');
            } finally {
                this.addLoading = false;
            }
        },

        async editProduct(id) {
            try {
                const response = await fetch(`/api/admin/products/${id}`);
                const data = await response.json();

                if (data.success) {
                    this.form = {
                        id: data.data.id,
                        name: data.data.name,
                        price: data.data.price,
                        cost_price: data.data.cost_price,
                        wholesale: data.data.wholesale || 0,
                        wholesale_unit: data.data.wholesale_unit || '',
                        wholesale_qty_per_unit: data.data.wholesale_qty_per_unit || 1,
                        stock: data.data.stock,
                        is_active: data.data.is_active,
                        tags: data.data.tags || []
                    };
                    this.tagsInput = (data.data.tags || []).join(', ');
                    this.errors = {};
                    
                    this.$dispatch('open-product-edit');
                }
            } catch (error) {
                console.error('Error fetching product:', error);
                this.showToast('Gagal memuat data produk', 'error');
            }
        },

        async updateProduct() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch(`/api/admin/products/${this.form.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    // Update product in list
                    const index = this.products.findIndex(p => p.id === this.form.id);
                    if (index !== -1) {
                        this.products[index] = data.data;
                    }

                    this.$dispatch('close-product-edit');
                    this.showToast('Produk berhasil diperbarui', 'success');
                    
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                } else {
                    this.errors = data.errors || {};
                    this.showToast(data.message || 'Gagal memperbarui produk', 'error');
                }
            } catch (error) {
                console.error('Error updating product:', error);
                this.showToast('Terjadi kesalahan saat menyimpan', 'error');
            } finally {
                this.loading = false;
            }
        },

        async deleteProduct(id, name) {
            if (!confirm(`Apakah Anda yakin ingin menghapus produk "${name}"?`)) {
                return;
            }

            try {
                const response = await fetch(`/api/admin/products/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.products = this.products.filter(p => p.id !== id);
                    this.showToast('Produk berhasil dihapus', 'success');
                } else {
                    this.showToast(data.message || 'Gagal menghapus produk', 'error');
                }
            } catch (error) {
                console.error('Error deleting product:', error);
                this.showToast('Terjadi kesalahan saat menghapus', 'error');
            }
        },

        updateTags() {
            if (this.tagsInput.trim()) {
                this.form.tags = this.tagsInput
                    .split(',')
                    .map(tag => tag.trim())
                    .filter(tag => tag.length > 0);
            } else {
                this.form.tags = [];
            }
            
            this.$nextTick(() => {
                lucide.createIcons();
            });
        },

        removeTag(index) {
            this.form.tags.splice(index, 1);
            this.tagsInput = this.form.tags.join(', ');
            
            this.$nextTick(() => {
                lucide.createIcons();
            });
        },

        updateAddTags() {
            if (this.addTagsInput.trim()) {
                this.addForm.tags = this.addTagsInput
                    .split(',')
                    .map(tag => tag.trim())
                    .filter(tag => tag.length > 0);
            } else {
                this.addForm.tags = [];
            }
            
            this.$nextTick(() => {
                lucide.createIcons();
            });
        },

        removeAddTag(index) {
            this.addForm.tags.splice(index, 1);
            this.addTagsInput = this.addForm.tags.join(', ');
            
            this.$nextTick(() => {
                lucide.createIcons();
            });
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            
            this.$nextTick(() => {
                lucide.createIcons();
            });

            setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        }
    }));
});
</script>
@endpush
@endsection
