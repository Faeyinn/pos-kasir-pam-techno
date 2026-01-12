/**
 * Kasir System Logic
 * Organized into modules for better maintainability.
 */

document.addEventListener("alpine:init", () => {
    Alpine.data("kasirSystem", () => ({
        // --- State ---
        searchQuery: "",
        selectedCategory: "all",
        selectedTags: [],
        cart: [],
        showPaymentModal: false,
        showReceiptModal: false,
        showClearCartModal: false,
        showHistoryModal: false,
        mobileCartOpen: false,
        receiptData: null,
        transactionHistory: [],
        products: [],
        loading: false,
        notifications: [],
        paymentType: "retail", // Default payment type

        // --- Initialization ---
        init() {
            this.fetchProducts();
            this.fetchTransactionHistory();

            this.$watch("selectedCategory", () =>
                this.$nextTick(() => window.lucide && lucide.createIcons())
            );
            this.$watch("searchQuery", () =>
                this.$nextTick(() => window.lucide && lucide.createIcons())
            );
        },

        // --- Notifications Module ---
        addNotification(message, type = "error") {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            this.$nextTick(() => window.lucide && lucide.createIcons());

            setTimeout(() => {
                this.notifications = this.notifications.filter(
                    (n) => n.id !== id
                );
            }, 4000);
        },

        // --- Products Module ---
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
                        tags: p.tags || [],
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
                const query = this.searchQuery.toLowerCase();

                // 1. Logic Pencarian Manual (Search Bar) - Expanded
                const matchName = p.name.toLowerCase().includes(query);
                const matchCategoryInSearch = p.category.toLowerCase().includes(query);
                const matchTagInSearch = p.tags && p.tags.some(t => t.toLowerCase().includes(query));

                const matchSearch = !query || matchName || matchCategoryInSearch || matchTagInSearch;

                // 2. Logic Kategori Tab (e.g. Minuman, Makanan)
                const matchCategory =
                    this.selectedCategory === "all" ||
                    p.category === this.selectedCategory;

                // 3. Logic Filter Dropdown (Multi-Tags AND Logic)
                const matchTags =
                    this.selectedTags.length === 0 ||
                    this.selectedTags.every(
                        (tag) => p.tags && p.tags.includes(tag)
                    );

                // 4. Syarat Akhir: (Logic Search) DAN (Logic Kategori) DAN (Logic Multi-Tags)
                return matchSearch && matchCategory && matchTags;
            });
        },

        get uniqueTags() {
            const tags = new Set();
            this.products.forEach((p) => {
                if (p.tags) p.tags.forEach((t) => tags.add(t));
            });
            return Array.from(tags).sort();
        },

        toggleTag(tag) {
            if (this.selectedTags.includes(tag)) {
                this.selectedTags = this.selectedTags.filter((t) => t !== tag);
            } else {
                this.selectedTags.push(tag);
            }
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        resetTags() {
            this.selectedTags = [];
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        // --- Scanner Module ---
        handleBarcodeScan(code) {
            console.log("Handling scan:", code);
            // Search logic: ID or Name (Exact Match)
            const product = this.products.find(p =>
                p.id == code ||
                p.name.toLowerCase() === code.toLowerCase()
            );

            if (product) {
                this.addToCart(product);
                this.addNotification(`Produk ditambahkan: ${product.name}`, 'success');

                // Optional: Play beep if not handled in modal
            } else {
                this.addNotification(`Produk tidak ditemukan: ${code}`, 'error');
            }
        },

        // --- Cart Module ---
        addToCart(product) {
            const existingItem = this.cart.find(
                (item) => item.id === product.id
            );
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
                const multiplier = isWholesaleMode
                    ? item.wholesaleQtyPerUnit
                    : 1;

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

        // --- Transactions Module ---
        async fetchTransactionHistory() {
            try {
                const response = await fetch("/api/transactions");
                const data = await response.json();
                if (data.success) {
                    this.transactionHistory = data.data.map((t) => ({
                        transactionNumber: t.transaction_number,
                        date: new Date(t.created_at).toLocaleDateString(
                            "id-ID",
                            { day: "2-digit", month: "long", year: "numeric" }
                        ),
                        time: new Date(t.created_at).toLocaleTimeString(
                            "id-ID",
                            { hour: "2-digit", minute: "2-digit" }
                        ),
                        cashier: t.user.name,
                        paymentMethod: t.payment_method,
                        amountReceived: t.amount_received,
                        change: t.change,
                        items: t.items.map((item) => ({
                            name: item.product_name,
                            qty: item.qty,
                            finalPrice: item.price,
                        })),
                        paymentType: t.payment_type,
                        subtotal: t.subtotal,
                        total: t.total,
                    }));
                }
            } catch (error) {
                console.error("Error fetching transaction history:", error);
            }
        },

        async confirmPayment() {
            const paymentModalEl = document.querySelector(
                '[x-data*="selectedPaymentMethod"]'
            );
            const paymentModalData = paymentModalEl
                ? Alpine.$data(paymentModalEl)
                : {};

            const amountReceived = parseFloat(
                paymentModalData.amountReceived || 0
            );
            const change = amountReceived - this.cartTotal;

            if (change < 0) {
                this.addNotification(
                    "Jumlah uang yang diterima kurang dari total"
                );
                return;
            }

            if (this.cart.length === 0) {
                this.addNotification("Keranjang masih kosong");
                return;
            }

            const transactionData = {
                payment_type: this.paymentType,
                payment_method:
                    paymentModalData.selectedPaymentMethod || "tunai",
                amount_received: amountReceived,
                items: this.cart.map((item) => ({
                    product_id: item.id,
                    qty: item.qty,
                    price: this.getItemPrice(item),
                })),
            };

            try {
                const response = await fetch("/api/transactions", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: JSON.stringify(transactionData),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message || "Gagal menyimpan transaksi"
                    );
                }

                const transaction = data.data;
                const now = new Date(transaction.created_at);

                const newReceipt = {
                    transactionNumber: transaction.transaction_number,
                    date: now.toLocaleDateString("id-ID", {
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                    }),
                    time: now.toLocaleTimeString("id-ID", {
                        hour: "2-digit",
                        minute: "2-digit",
                    }),
                    cashier: transaction.user.name,
                    paymentMethod: transaction.payment_method,
                    amountReceived: transaction.amount_received,
                    change: transaction.change,
                    items: (transaction.items || []).map((item) => ({
                        name: item.product_name,
                        qty: item.qty,
                        finalPrice: item.price,
                    })),
                    paymentType: transaction.payment_type,
                    subtotal: transaction.subtotal,
                    total: transaction.total,
                };

                this.receiptData = newReceipt;
                this.showPaymentModal = false;

                this.$nextTick(() => {
                    this.showReceiptModal = true;
                    window.lucide && lucide.createIcons();
                });

                await this.fetchProducts();
                this.transactionHistory.unshift(this.receiptData);
                this.$nextTick(() => window.lucide && lucide.createIcons());
            } catch (error) {
                console.error("Error saving transaction:", error);
                this.addNotification(
                    error.message || "Gagal menyimpan transaksi"
                );
            }
        },

        finishTransaction() {
            this.cart = [];
            this.showReceiptModal = false;
            this.receiptData = null;
        },

        viewTransactionDetail(transaction) {
            this.receiptData = transaction;
            this.showHistoryModal = false;
            this.showReceiptModal = true;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        // --- Print & Utilities Module ---
        reprintReceipt(transaction) {
            this.receiptData = transaction;
            this.showHistoryModal = false;
            this.showReceiptModal = true;
            this.$nextTick(() => {
                window.lucide && lucide.createIcons();
                setTimeout(() => window.print(), 500);
            });
        },

        formatNumber(number) {
            return new Intl.NumberFormat("id-ID").format(number);
        },

        printReceipt() {
            if (!this.receiptData) return;

            const printWindow = window.open(
                "",
                "_blank",
                "width=300,height=600"
            );
            const receipt = this.receiptData;

            let itemsHTML = "";
            receipt.items.forEach((item) => {
                const itemTotal = this.formatNumber(item.qty * item.finalPrice);
                const itemPrice = this.formatNumber(item.finalPrice);
                itemsHTML += `<div style="margin-bottom: 8px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                        <span style="font-weight: bold; flex: 1;">${item.name}</span>
                        <span style="font-weight: bold; white-space: nowrap;">Rp ${itemTotal}</span>
                    </div>
                    <div style="font-size: 8pt; color: #666;">
                        ${item.qty} x Rp ${itemPrice}
                    </div>
                </div>`;
            });

            const paymentMethodText =
                receipt.paymentMethod === "tunai"
                    ? "Tunai"
                    : receipt.paymentMethod === "kartu"
                        ? "Kartu"
                        : receipt.paymentMethod === "qris"
                            ? "QRIS"
                            : "E-Wallet";

            const transactionType =
                this.paymentType === "wholesale" ? "GROSIR" : "RETAIL";

            const htmlContent = `<!DOCTYPE html>
                <html>
                <head>
                    <title>Struk - ${receipt.transactionNumber}</title>
                    <style>
                    @page { margin: 10mm; size: 80mm auto; }
                    body { font-family: "Courier New", monospace; font-size: 9pt; line-height: 1.3; margin: 0; padding: 8px; width: 80mm; color: #000; }
                    .center { text-align: center; }
                    .separator { text-align: center; margin: 4px 0; letter-spacing: -1px; }
                    .row { display: flex; justify-content: space-between; margin: 2px 0; }
                    .bold { font-weight: bold; }
                    h1 { font-size: 14pt; font-weight: bold; margin: 2px 0; letter-spacing: 0.5px; }
                    .subtitle { font-size: 8pt; margin: 2px 0; }
                    .grand-total { font-size: 12pt; font-weight: bold; margin-top: 4px; }
                    </style>
                </head>
                <body>
                    <div class="center">
                        <h1>PAM TECHNO</h1>
                        <div class="subtitle">Sistem Kasir Digital</div>
                        <div class="separator">================================</div>
                    </div>
                    <div style="margin: 8px 0;">
                        <div class="row"><span>No. Transaksi</span><span class="bold">${receipt.transactionNumber
                }</span></div>
                        <div class="row"><span>Tanggal</span><span>${receipt.date
                }</span></div>
                        <div class="row"><span>Waktu</span><span>${receipt.time
                }</span></div>
                        <div class="row"><span>Kasir</span><span>${receipt.cashier
                }</span></div>
                        <div class="row"><span>Jenis</span><span class="bold">${transactionType}</span></div>
                    </div>
                    <div class="separator">================================</div>
                    <div style="margin: 8px 0;">${itemsHTML}</div>
                    <div class="separator">================================</div>
                    <div style="margin: 8px 0;">
                        <div class="row"><span>Total</span><span class="bold">Rp ${this.formatNumber(
                    receipt.total
                )}</span></div>
                        <div class="row"><span>Bayar</span><span>${paymentMethodText}</span></div>
                        <div class="row"><span>Diterima</span><span>Rp ${this.formatNumber(
                    receipt.amountReceived
                )}</span></div>
                        <div class="row"><span>Kembalian</span><span class="bold">Rp ${this.formatNumber(
                    receipt.change
                )}</span></div>
                    </div>
                    <div class="separator">================================</div>
                    <div class="row grand-total"><span>GRAND TOTAL</span><span>Rp ${this.formatNumber(
                    receipt.total
                )}</span></div>
                    <div class="separator">================================</div>
                    <div class="center" style="margin-top: 8px;">
                        <div class="subtitle">Terima kasih atas kunjungan Anda</div>
                        <div class="subtitle">Barang yang sudah dibeli</div>
                        <div class="subtitle">tidak dapat dikembalikan</div>
                    </div>
                </body>
                </html>`;

            printWindow.document.write(htmlContent);
            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        },
    }));
});
