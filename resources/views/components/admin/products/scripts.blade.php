<script>
(function registerProductManager() {
    const register = () => {
        if (!window.Alpine) return;

        // Register Alpine component (optional). The page can also use x-data="productManager()".
        window.Alpine.data('productManager', window.productManager);
    };

    // Global factory so x-data can call it directly.
    // This avoids race conditions around Alpine.data registration.
    window.productManager = window.productManager || (() => ({
        products: window.__PRODUCTS_DATA__ || [],
        availableTags: window.__TAGS_DATA__ || [],
        search: '',
        
        currentPage: 1,
        perPage: 10,

        apiFetch(url, options = {}) {
            return fetch(url, {
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(options.headers || {}),
                },
                ...options,
            });
        },

        async reloadProducts() {
            try {
                const response = await this.apiFetch('/api/admin/products');
                const data = await response.json();
                if (data.success) {
                    this.products = data.data || [];
                    this.availableTags = data.tags || this.availableTags;
                    if (this.currentPage > this.totalPages) this.currentPage = this.totalPages || 1;
                    this.$nextTick(() => lucide.createIcons());
                }
            } catch (e) {
                // ignore; keep existing in-memory state
            }
        },

        async init() {
            this.$nextTick(() => lucide.createIcons());
            document.addEventListener('tags-updated', (e) => {
                this.availableTags = e.detail;
            });

            // Watch for changes that affect displayed products and refresh icons
            this.$watch('search', () => {
                this.currentPage = 1;
                this.$nextTick(() => lucide.createIcons());
            });

            // Ensure the list is always fresh (avoid stale Blade-injected data)
            await this.reloadProducts();
        },

        // Indonesian-first getters (backward-compatible with legacy keys)
        getProductName(product) {
            return product?.nama_produk ?? product?.name ?? '';
        },

        getRetailPrice(product) {
            return Number(product?.harga_jual ?? product?.price ?? 0);
        },

        getCostPrice(product) {
            return Number(product?.harga_pokok ?? product?.cost_price ?? 0);
        },

        getWholesalePrice(product) {
            return Number(product?.harga_jual_grosir ?? product?.wholesale ?? 0);
        },

        getWholesaleUnit(product) {
            return product?.nama_satuan_grosir ?? product?.wholesale_unit ?? '';
        },

        getWholesaleQtyPerUnit(product) {
            return Number(product?.jumlah_per_satuan_grosir ?? product?.wholesale_qty_per_unit ?? 1);
        },

        getStock(product) {
            return Number(product?.stok ?? product?.stock ?? 0);
        },
        
        get filteredProducts() {
            if (!this.search) return this.products;
            const q = this.search.toLowerCase();
            return this.products.filter(p => 
                this.getProductName(p).toLowerCase().includes(q) ||
                (p.tags && p.tags.some(t => t.name.toLowerCase().includes(q)))
            );
        },

        get paginatedProducts() {
            const filtered = this.filteredProducts;
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return filtered.slice(start, end);
        },

        get totalPages() {
            return Math.ceil(this.filteredProducts.length / this.perPage);
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.$nextTick(() => lucide.createIcons());
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.$nextTick(() => lucide.createIcons());
            }
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.$nextTick(() => lucide.createIcons());
            }
        },

        getActiveDiscount(product) {
            if (!product.discounts || product.discounts.length === 0) return null;
            return product.discounts[0];
        },

        getDiscountedPrice(product) {
            const discount = this.getActiveDiscount(product);
            const retailPrice = this.getRetailPrice(product);
            if (!discount) return retailPrice;

            if (discount.type === 'percentage') {
                return retailPrice - (retailPrice * discount.value / 100);
            } else {
                return Math.max(0, retailPrice - discount.value);
            }
        },

        getDiscountPercentage(product) {
            const discount = this.getActiveDiscount(product);
            if (!discount) return 0;

            const retailPrice = this.getRetailPrice(product);

            if (discount.type === 'percentage') {
                return discount.value;
            } else {
                if (retailPrice <= 0) return 0;
                return Math.round((discount.value / retailPrice) * 100);
            }
        },

        form: {
            id: null, name: '', price: 0, cost_price: 0, wholesale: 0,
            wholesale_unit: '', wholesale_qty_per_unit: 1, stock: 0,
            is_active: true, tag_ids: []
        },
        errors: {},
        loading: false,
        
        addForm: {
            name: '', price: 0, cost_price: 0, wholesale: 0,
            wholesale_unit: '', wholesale_qty_per_unit: 1, stock: 0,
            is_active: true, tag_ids: []
        },
        addErrors: {},
        addLoading: false,
        
        toast: { show: false, message: '', type: 'success' },

        toggleTagList(arrayPath, tagId) {
            if (!tagId) return;
            let parts = arrayPath.split('.');
            let target = this;
            
            for (let i = 0; i < parts.length - 1; i++) {
                if (target[parts[i]]) {
                    target = target[parts[i]];
                } else {
                    return;
                }
            }
            
            let key = parts[parts.length - 1];
            if (!Array.isArray(target[key])) target[key] = [];
            
            if (target[key].includes(tagId)) {
                target[key] = target[key].filter(id => id !== tagId);
            } else {
                target[key].push(tagId);
            }
            this.$nextTick(() => lucide.createIcons());
        },

        getTag(id) {
            return this.availableTags.find(t => t.id === id);
        },

        openAddModal() {
            this.addForm = {
                name: '', price: 0, cost_price: 0, wholesale: 0,
                wholesale_unit: '', wholesale_qty_per_unit: 1, stock: 0,
                is_active: true, tag_ids: []
            };
            this.addErrors = {};
            this.$dispatch('open-product-add');
        },

        async createProduct() {
            this.addLoading = true;
            this.addErrors = {};

            try {
                const response = await this.apiFetch('/api/admin/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.addForm)
                });

                const data = await response.json();

                if (data.success) {
                    await this.reloadProducts();
                    this.$dispatch('close-product-add');
                    this.showToast('Produk berhasil ditambahkan', 'success');
                    this.$nextTick(() => lucide.createIcons());
                } else {
                    this.addErrors = data.errors || {};
                    this.showToast(data.message || 'Gagal menambahkan produk', 'error');
                }
            } catch (error) {
                this.showToast('Terjadi kesalahan saat menyimpan', 'error');
            } finally {
                this.addLoading = false;
            }
        },

        async editProduct(id) {
            try {
                const response = await this.apiFetch(`/api/admin/products/${id}`);
                const data = await response.json();

                if (data.success) {
                    this.form = {
                        id: data.data.id,
                        name: this.getProductName(data.data),
                        price: this.getRetailPrice(data.data),
                        cost_price: this.getCostPrice(data.data),
                        wholesale: this.getWholesalePrice(data.data),
                        wholesale_unit: this.getWholesaleUnit(data.data),
                        wholesale_qty_per_unit: this.getWholesaleQtyPerUnit(data.data),
                        stock: this.getStock(data.data),
                        is_active: data.data.is_active,
                        tag_ids: (data.data.tag_ids || (data.data.tags || []).map(t => t.id) || [])
                    };
                    this.errors = {};
                    this.$dispatch('open-product-edit');
                }
            } catch (error) {
                this.showToast('Gagal memuat data produk', 'error');
            }
        },

        async updateProduct() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await this.apiFetch(`/api/admin/products/${this.form.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    await this.reloadProducts();

                    this.$dispatch('close-product-edit');
                    this.showToast('Produk berhasil diperbarui', 'success');
                    this.$nextTick(() => lucide.createIcons());
                } else {
                    this.errors = data.errors || {};
                    this.showToast(data.message || 'Gagal memperbarui produk', 'error');
                }
            } catch (error) {
                this.showToast('Terjadi kesalahan saat menyimpan', 'error');
            } finally {
                this.loading = false;
            }
        },

        async deleteProduct(id, name) {
            if (!confirm(`Apakah Anda yakin ingin menghapus produk "${name}"?`)) return;

            try {
                const response = await this.apiFetch(`/api/admin/products/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });

                const data = await response.json();

                if (data.success) {
                    await this.reloadProducts();
                    this.showToast('Produk berhasil dihapus', 'success');
                } else {
                    this.showToast(data.message || 'Gagal menghapus produk', 'error');
                }
            } catch (error) {
                this.showToast('Terjadi kesalahan saat menghapus', 'error');
            }
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            this.$nextTick(() => lucide.createIcons());
            setTimeout(() => { this.toast.show = false; }, 3000);
        }
    }));

    const ensureInitTree = () => {
        if (!window.Alpine || typeof window.Alpine.initTree !== 'function') return;
        document.querySelectorAll('[x-data="productManager"]').forEach((el) => {
            // If Alpine already initialized this element, skip.
            if (el._x_dataStack) return;
            window.Alpine.initTree(el);
        });
    };

    // If Alpine is already loaded/started, register immediately.
    // Otherwise, hook into alpine:init.
    document.addEventListener('alpine:init', () => {
        register();
    });

    // If this script is loaded after Alpine.start(), we won't get a second alpine:init.
    // Register immediately and re-init the tree.
    if (window.Alpine) {
        register();
        ensureInitTree();
    }

    // Also attempt after Alpine finishes initializing the page.
    document.addEventListener('alpine:initialized', () => {
        register();
        ensureInitTree();
    });
})();
</script>
