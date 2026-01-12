export const cartModule = {
    cart: [],

    addToCart(product) {
        const existingItem = this.cart.find((item) => item.id === product.id);
        const currentQty = existingItem ? existingItem.qty : 0;

        const isWholesale =
            this.paymentType === "wholesale" && product.wholesale > 0;
        const multiplier = isWholesale ? product.wholesaleQtyPerUnit : 1;
        const totalPcsNeeded = (currentQty + 1) * multiplier;

        if (totalPcsNeeded > product.stock) {
            if (isWholesale) {
                const availableWholesale = Math.floor(
                    product.stock / product.wholesaleQtyPerUnit
                );
                this.addNotification(
                    `Stok tidak mencukupi! Sisa stok untuk ${product.name} adalah ${availableWholesale} ${product.wholesaleUnit}.`
                );
            } else {
                this.addNotification(
                    `Stok tidak mencukupi! Sisa stok untuk ${product.name} adalah ${product.stock} unit.`
                );
            }
            return;
        }

        if (existingItem) {
            existingItem.qty += 1;
        } else {
            this.cart.push({ ...product, qty: 1 });
        }
        this.$nextTick(() => window.lucide && lucide.createIcons());
    },

    updateQty(productId, delta) {
        const item = this.cart.find((i) => i.id === productId);
        if (item) {
            const isWholesaleMode =
                this.paymentType === "wholesale" && item.wholesale > 0;
            const multiplier = isWholesaleMode ? item.wholesaleQtyPerUnit : 1;

            if (delta > 0 && (item.qty + delta) * multiplier > item.stock) {
                if (isWholesaleMode) {
                    const availableWholesale = Math.floor(
                        item.stock / item.wholesaleQtyPerUnit
                    );
                    this.addNotification(
                        `Stok tidak mencukupi! Sisa stok untuk ${item.name} adalah ${availableWholesale} ${item.wholesaleUnit}.`
                    );
                } else {
                    this.addNotification(
                        `Stok tidak mencukupi! Sisa stok untuk ${item.name} adalah ${item.stock} unit.`
                    );
                }
                return;
            }
            item.qty += delta;
            if (item.qty <= 0) {
                this.removeFromCart(productId);
            }
        }
    },

    removeFromCart(productId) {
        this.cart = this.cart.filter((item) => item.id !== productId);
        this.$nextTick(() => window.lucide && lucide.createIcons());
    },

    clearCart() {
        this.showClearCartModal = true;
        this.$nextTick(() => window.lucide && lucide.createIcons());
    },

    getItemPrice(item) {
        return this.isWholesale(item) ? item.wholesale : item.price;
    },

    get cartTotal() {
        return this.cart.reduce(
            (total, item) => total + this.getItemPrice(item) * item.qty,
            0
        );
    },

    get canApplyWholesale() {
        return this.cart.some((item) => item.wholesale > 0);
    },

    isWholesale(item) {
        return (
            (this.paymentType === "wholesale" && item.wholesale > 0) ||
            (item.wholesale > 0 && item.qty >= item.minWholesale)
        );
    },
};
