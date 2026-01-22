<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productManager', () => ({
        products: window.__PRODUCTS_DATA__ || [],
        availableTags: window.__TAGS_DATA__ || [],
        search: '',
        
        currentPage: 1,
        perPage: 10,

        init() {
            this.$nextTick(() => lucide.createIcons());
            document.addEventListener('tags-updated', (e) => {
                this.availableTags = e.detail;
            });
        },
        
        get filteredProducts() {
            if (!this.search) return this.products;
            const q = this.search.toLowerCase();
            return this.products.filter(p => 
                p.name.toLowerCase().includes(q) ||
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
            if (this.currentPage < this.totalPages) this.currentPage++;
        },

        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) this.currentPage = page;
        },

        getActiveDiscount(product) {
            if (!product.discounts || product.discounts.length === 0) return null;
            return product.discounts[0];
        },

        getDiscountedPrice(product) {
            const discount = this.getActiveDiscount(product);
            if (!discount) return product.price;

            if (discount.type === 'percentage') {
                return product.price - (product.price * discount.value / 100);
            } else {
                return Math.max(0, product.price - discount.value);
            }
        },

        getDiscountPercentage(product) {
            const discount = this.getActiveDiscount(product);
            if (!discount) return 0;

            if (discount.type === 'percentage') {
                return discount.value;
            } else {
                return Math.round((discount.value / product.price) * 100);
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
                    this.products.push(data.data);
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
                        tag_ids: (data.data.tags || []).map(t => t.id)
                    };
                    this.tagsInput = (data.data.tags || []).join(', ');
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
                    const index = this.products.findIndex(p => p.id === this.form.id);
                    if (index !== -1) this.products[index] = data.data;

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
                const response = await fetch(`/api/admin/products/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });

                const data = await response.json();

                if (data.success) {
                    this.products = this.products.filter(p => p.id !== id);
                    this.showToast('Produk berhasil dihapus', 'success');
                } else {
                    this.showToast(data.message || 'Gagal menghapus produk', 'error');
                }
            } catch (error) {
                this.showToast('Terjadi kesalahan saat menghapus', 'error');
            }
        },

        updateTags() {
            if (this.tagsInput.trim()) {
                this.form.tags = this.tagsInput.split(',').map(tag => tag.trim()).filter(tag => tag.length > 0);
            } else {
                this.form.tags = [];
            }
            this.$nextTick(() => lucide.createIcons());
        },

        removeTag(index) {
            this.form.tags.splice(index, 1);
            this.tagsInput = this.form.tags.join(', ');
            this.$nextTick(() => lucide.createIcons());
        },

        updateAddTags() {
            if (this.addTagsInput.trim()) {
                this.addForm.tags = this.addTagsInput.split(',').map(tag => tag.trim()).filter(tag => tag.length > 0);
            } else {
                this.addForm.tags = [];
            }
            this.$nextTick(() => lucide.createIcons());
        },

        removeAddTag(index) {
            this.addForm.tags.splice(index, 1);
            this.addTagsInput = this.addForm.tags.join(', ');
            this.$nextTick(() => lucide.createIcons());
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            this.$nextTick(() => lucide.createIcons());
            setTimeout(() => { this.toast.show = false; }, 3000);
        }
    }));
});
</script>
