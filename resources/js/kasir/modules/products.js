export const productsModule = {
    searchQuery: "",
    selectedCategory: "all",
    products: [],
    loading: false,

    async fetchProducts() {
        this.loading = true;
        try {
            const response = await fetch("/api/products");
            const data = await response.json();
            if (data.success) {
                this.products = data.data.map((p) => ({
                    id: p.id,
                    name: p.name,
                    image: p.image,
                    price: p.price,
                    wholesale: p.wholesale,
                    wholesaleUnit: p.wholesale_unit,
                    wholesaleQtyPerUnit: p.wholesale_qty_per_unit,
                    category: p.category,
                    stock: p.stock,
                }));
            }
        } catch (error) {
            console.error("Error fetching products:", error);
            this.addNotification("Gagal memuat data produk");
        } finally {
            this.loading = false;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        }
    },

    get filteredProducts() {
        return this.products.filter((p) => {
            const matchSearch = p.name
                .toLowerCase()
                .includes(this.searchQuery.toLowerCase());
            const matchCategory =
                this.selectedCategory === "all" ||
                p.category === this.selectedCategory;
            return matchSearch && matchCategory;
        });
    },
};
