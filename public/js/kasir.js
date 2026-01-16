/**
 * Kasir System Logic
 * Organized into modules for better maintainability.
 */

document.addEventListener("alpine:init", () => {
    Alpine.data("kasirSystem", () => ({
        // --- State ---
        searchQuery: "",

        searchQuery: "",
        viewMode: "grid", // 'grid' | 'list'

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

                        stock: p.stock,
                        tags: p.tags || [],
                        // Calculated field for price per piece when wholesale condition met
                        wholesalePricePerPiece:
                            p.wholesale_qty_per_unit > 0
                                ? p.wholesale / p.wholesale_qty_per_unit
                                : p.price,
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

                const matchTagInSearch =
                    p.tags &&
                    p.tags.some((t) => t.toLowerCase().includes(query));

                const matchSearch = !query || matchName || matchTagInSearch;

                // 3. Logic Filter Dropdown (Multi-Tags AND Logic)
                const matchTags =
                    this.selectedTags.length === 0 ||
                    this.selectedTags.every(
                        (tag) => p.tags && p.tags.includes(tag)
                    );

                // 4. Syarat Akhir: (Logic Search) DAN (Logic Multi-Tags)
                return matchSearch && matchTags;
            });
        },

        get uniqueTags() {
            const tags = new Set();
            this.products.forEach((p) => {
                if (p.tags) p.tags.forEach((t) => tags.add(t));
            });
            return Array.from(tags).sort();
        },

        get popularTags() {
            // Count tag frequency
            const tagCounts = {};
            this.products.forEach((p) => {
                if (p.tags) {
                    p.tags.forEach((t) => {
                        tagCounts[t] = (tagCounts[t] || 0) + 1;
                    });
                }
            });

            // Sort by frequency and take top 5
            return Object.entries(tagCounts)
                .sort((a, b) => b[1] - a[1]) // Sort descending by count
                .slice(0, 5) // Take top 5
                .map((entry) => entry[0]); // Return tag names
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
            const product = this.products.find(
                (p) =>
                    p.id == code || p.name.toLowerCase() === code.toLowerCase()
            );

            if (product) {
                this.addToCart(product);
                this.addNotification(
                    `Produk ditambahkan: ${product.name}`,
                    "success"
                );

                // Optional: Play beep if not handled in modal
            } else {
                this.addNotification(
                    `Produk tidak ditemukan: ${code}`,
                    "error"
                );
            }
        },

        // --- Cart Module ---
        addToCart(product) {
            const existingItem = this.cart.find(
                (item) => item.id === product.id
            );

            // Logic baru: Selalu tambah 1 unit (retail base)
            // Jika quantity mencapai grosir, harga otomatis berubah di isWholesale/getItemPrice
            const requestQty = 1;

            if (existingItem) {
                // Check stock for increment
                if (existingItem.qty + requestQty > existingItem.stock) {
                    this.addNotification(
                        `Stok tidak mencukupi! Sisa stok untuk ${product.name} adalah ${existingItem.stock} unit.`
                    );
                    return;
                }
                existingItem.qty += requestQty;
            } else {
                if (requestQty > product.stock) {
                    this.addNotification(
                        `Stok tidak mencukupi! Sisa stok untuk ${product.name} adalah ${product.stock} unit.`
                    );
                    return;
                }
                this.cart.push({ ...product, qty: requestQty });
            }
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        updateQty(productId, delta) {
            const item = this.cart.find((i) => i.id === productId);
            if (item) {
                if (delta > 0 && item.qty + delta > item.stock) {
                    this.addNotification(
                        `Stok tidak mencukupi! Sisa stok untuk ${item.name} adalah ${item.stock} unit.`
                    );
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
            return this.isWholesale(item)
                ? item.wholesalePricePerPiece
                : item.price;
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
            // Otomatis grosir jika qty >= wholesaleQtyPerUnit dan fitur grosir tersedia
            return (
                item.wholesale > 0 &&
                item.wholesaleQtyPerUnit > 0 &&
                item.qty >= item.wholesaleQtyPerUnit
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

            const effectivePaymentType = this.cart.some((item) =>
                this.isWholesale(item)
            )
                ? "wholesale"
                : "retail";

            const transactionData = {
                payment_type: effectivePaymentType,
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
                // Grid layout for precise alignment
                itemsHTML += `<div style="margin-bottom: 5px;">
                    <div style="font-weight: bold; margin-bottom: 2px;">${item.name}</div>
                    <div style="display: grid; grid-template-columns: 1fr auto; width: 100%;">
                        <div>${item.qty} x ${itemPrice}</div>
                        <div style="text-align: right; font-weight: bold;">${itemTotal}</div>
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
                receipt.paymentType === "wholesale" ? "GROSIR" : "RETAIL";

            // Ultra-simple HTML for thermal printers
            // Uses only basic fonts and black colors
            const htmlContent = `<!DOCTYPE html>
                <html>
                <head>
                    <title>Struk - ${receipt.transactionNumber}</title>
                    <style>
                        @page { margin: 0; size: 80mm auto; }
                        body { 
                            font-family: 'Courier New', Courier, monospace; 
                            font-size: 11px; 
                            line-height: 1.3; 
                            margin: 0; 
                            padding: 15px 10px; 
                            width: 100%;
                            background: #fff;
                            color: #000;
                            box-sizing: border-box;
                        }
                        .center { text-align: center; }
                        .right { text-align: right; }
                        .flex-between { display: flex; justify-content: space-between; align-items: center; }
                        .grid-2 { display: grid; grid-template-columns: 1fr auto; width: 100%; }
                        .bold { font-weight: bold; }
                        .divider { border-top: 1px dashed #333; margin: 8px 0; }
                        .header { margin-bottom: 12px; }
                        .footer { margin-top: 12px; font-size: 10px; color: #333; }
                        .mb-1 { margin-bottom: 4px; }
                        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; box-sizing: border-box; }
                    </style>
                </head>
                <body>
                    <!-- Header -->
                    <div class="center header">
                        <div class="bold" style="font-size: 18px; margin-bottom: 4px;">PAM TECHNO</div>
                        <div style="font-size: 11px;">Jalan Raya Gadut, Lubuk Kilangan</div>
                        <div style="font-size: 11px;">Padang, Sumatera Barat</div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <!-- Meta Info -->
                    <div class="grid-2 mb-1">
                        <div>No Transaksi</div>
                        <div class="bold">${receipt.transactionNumber}</div>
                    </div>
                    <div class="grid-2 mb-1">
                        <div>Tanggal</div>
                        <div>${receipt.date}</div>
                    </div>
                    <div class="grid-2 mb-1">
                        <div>Waktu</div>
                        <div>${receipt.time}</div>
                    </div>
                    <div class="grid-2 mb-1">
                        <div>Kasir</div>
                        <div>${receipt.cashier}</div>
                    </div>
                    </div>
                     <div class="grid-2 mb-1">
                        <div>Tipe</div>
                        <div class="bold">${transactionType}</div>
                    </div>
                     <div class="grid-2 mb-1">
                        <div>Pembayaran</div>
                        <div class="bold">${paymentMethodText}</div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <!-- Items -->
                    <div style="margin: 8px 0;">${itemsHTML}</div>
                    
                    <div class="divider"></div>
                    
                    <!-- Totals -->
                    <div class="grid-2 mb-1">
                        <div>Total</div>
                        <div class="bold">Rp ${this.formatNumber(
                            receipt.total
                        )}</div>
                    </div>
                    <div class="grid-2 mb-1">
                        <div>Bayar (${paymentMethodText})</div>
                        <div>Rp ${this.formatNumber(
                            receipt.amountReceived
                        )}</div>
                    </div>
                    <div class="grid-2 mb-1">
                        <div>Kembali</div>
                        <div class="bold">Rp ${this.formatNumber(
                            receipt.change
                        )}</div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <!-- Grand Total Highlight -->
                    <div class="center" style="margin: 10px 0;">
                        <div style="font-size: 12px; margin-bottom: 2px;">GRAND TOTAL</div>
                        <div class="bold" style="font-size: 20px;">Rp ${this.formatNumber(
                            receipt.total
                        )}</div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <!-- Footer -->
                    <div class="center footer">
                        <div>Terima kasih atas kunjungan Anda</div>
                        <div>Barang yang sudah dibeli</div>
                        <div>tidak dapat dikembalikan</div>
                        <br>
                        <div>-- Layanan Pelanggan --</div>
                        <div>0812-3456-7890</div>
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
