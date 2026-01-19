document.addEventListener("alpine:init", () => {
    Alpine.data("kasirSystem", () => ({
        // State
        searchQuery: "",
        viewMode: "grid",
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
        paymentType: "retail",
        availableTags: [],

        // Initialize
        init() {
            this.fetchTags();
            this.fetchProducts();
            this.fetchTransactionHistory();

            this.$watch("searchQuery", () =>
                this.$nextTick(() => window.lucide && lucide.createIcons()),
            );
        },

        // ==================== NOTIFICATIONS ====================
        addNotification(message, type = "error") {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            this.$nextTick(() => window.lucide && lucide.createIcons());

            setTimeout(() => {
                this.notifications = this.notifications.filter(
                    (n) => n.id !== id,
                );
            }, 4000);
        },

        // ==================== PRODUCTS ====================
        async fetchTags() {
            try {
                const response = await fetch("/api/tags");
                const data = await response.json();
                if (data.success) {
                    this.availableTags = data.data;
                }
            } catch (error) {
                console.error("Error fetching tags:", error);
            }
        },

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
                const matchName = p.name.toLowerCase().includes(query);
                const matchTagInSearch =
                    p.tags &&
                    p.tags.some((t) => t.name.toLowerCase().includes(query));
                const matchSearch = !query || matchName || matchTagInSearch;
                const matchTags =
                    this.selectedTags.length === 0 ||
                    this.selectedTags.every(
                        (selectedTagId) =>
                            p.tags &&
                            p.tags.some((t) => t.id === selectedTagId),
                    );
                return matchSearch && matchTags;
            });
        },

        get popularTags() {
            const tagCounts = {};
            this.products.forEach((p) => {
                if (p.tags) {
                    p.tags.forEach((t) => {
                        tagCounts[t.id] = (tagCounts[t.id] || 0) + 1;
                    });
                }
            });
            return Object.entries(tagCounts)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 10)
                .map(([id]) => this.availableTags.find((t) => t.id == id))
                .filter((t) => t);
        },

        toggleTag(tagId) {
            if (this.selectedTags.includes(tagId)) {
                this.selectedTags = this.selectedTags.filter(
                    (t) => t !== tagId,
                );
            } else {
                this.selectedTags.push(tagId);
            }
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        resetTags() {
            this.selectedTags = [];
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        handleBarcodeScan(code) {
            console.log("Handling scan:", code);
            const product = this.products.find(
                (p) =>
                    p.id == code || p.name.toLowerCase() === code.toLowerCase(),
            );
            if (product) {
                this.addToCart(product);
                this.addNotification(
                    `Produk ditambahkan: ${product.name}`,
                    "success",
                );
            } else {
                this.addNotification(
                    `Produk tidak ditemukan: ${code}`,
                    "error",
                );
            }
        },

        // ==================== CART ====================
        addToCart(product) {
            const existingItem = this.cart.find(
                (item) => item.id === product.id,
            );
            const requestQty = 1;

            if (existingItem) {
                if (existingItem.qty + requestQty > existingItem.stock) {
                    this.addNotification(
                        `Stok tidak mencukupi! Sisa stok untuk ${product.name} adalah ${existingItem.stock} unit.`,
                    );
                    return;
                }
                existingItem.qty += requestQty;
            } else {
                if (requestQty > product.stock) {
                    this.addNotification(
                        `Stok tidak mencukupi! Sisa stok untuk ${product.name} adalah ${product.stock} unit.`,
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
                        `Stok tidak mencukupi! Sisa stok untuk ${item.name} adalah ${item.stock} unit.`,
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
                0,
            );
        },

        get canApplyWholesale() {
            return this.cart.some((item) => item.wholesale > 0);
        },

        isWholesale(item) {
            return (
                item.wholesale > 0 &&
                item.wholesaleQtyPerUnit > 0 &&
                item.qty >= item.wholesaleQtyPerUnit
            );
        },

        // ==================== TRANSACTIONS ====================
        async fetchTransactionHistory() {
            try {
                const response = await fetch("/api/transactions");
                const data = await response.json();
                if (data.success) {
                    this.transactionHistory = data.data.map((t) => ({
                        transactionNumber: t.transaction_number,
                        date: new Date(t.created_at).toLocaleDateString(
                            "id-ID",
                            {
                                day: "2-digit",
                                month: "long",
                                year: "numeric",
                            },
                        ),
                        time: new Date(t.created_at).toLocaleTimeString(
                            "id-ID",
                            {
                                hour: "2-digit",
                                minute: "2-digit",
                            },
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
                '[x-data*="selectedPaymentMethod"]',
            );
            const paymentModalData = paymentModalEl
                ? Alpine.$data(paymentModalEl)
                : {};

            const amountReceived = parseFloat(
                paymentModalData.amountReceived || 0,
            );
            const change = amountReceived - this.cartTotal;

            if (change < 0) {
                this.addNotification(
                    "Jumlah uang yang diterima kurang dari total",
                );
                return;
            }

            if (this.cart.length === 0) {
                this.addNotification("Keranjang masih kosong");
                return;
            }

            const effectivePaymentType = this.cart.some((item) =>
                this.isWholesale(item),
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
                            'meta[name="csrf-token"]',
                        ).content,
                    },
                    body: JSON.stringify(transactionData),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message || "Gagal menyimpan transaksi",
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
                    error.message || "Gagal menyimpan transaksi",
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

        // ==================== PRINT ====================
        reprintReceipt(transaction) {
            this.receiptData = transaction;
            this.showHistoryModal = false;
            this.showReceiptModal = true;
            this.$nextTick(() => {
                window.lucide && lucide.createIcons();
                setTimeout(() => this.printReceipt(), 500);
            });
        },

        formatNumber(number) {
            return new Intl.NumberFormat("id-ID").format(number);
        },

        padRight(str, length) {
            str = String(str);
            if (str.length >= length) return str.substring(0, length);
            return str + " ".repeat(length - str.length);
        },

        padLeft(str, length) {
            str = String(str);
            if (str.length >= length) return str.substring(str.length - length);
            return " ".repeat(length - str.length) + str;
        },

        centerText(str, width) {
            str = String(str);
            if (str.length >= width) return str.substring(0, width);
            const padding = Math.floor((width - str.length) / 2);
            return (
                " ".repeat(padding) +
                str +
                " ".repeat(width - str.length - padding)
            );
        },

        formatRow(left, right, width = 32) {
            const rightStr = String(right);
            const maxLeftWidth = width - rightStr.length - 1;
            const leftStr = this.padRight(left, maxLeftWidth);
            return leftStr + " " + rightStr;
        },

        wrapText(text, maxWidth) {
            const words = text.split(" ");
            const lines = [];
            let currentLine = "";

            words.forEach((word) => {
                if (currentLine.length + word.length + 1 <= maxWidth) {
                    currentLine += (currentLine ? " " : "") + word;
                } else {
                    if (currentLine) lines.push(currentLine);
                    currentLine =
                        word.length > maxWidth
                            ? word.substring(0, maxWidth)
                            : word;
                }
            });
            if (currentLine) lines.push(currentLine);
            return lines;
        },

        printReceipt() {
            if (!this.receiptData) return;

            const WIDTH = 32;
            const SEPARATOR = "=".repeat(WIDTH);
            const DASH_LINE = "-".repeat(WIDTH);
            const receipt = this.receiptData;

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

            let lines = [];

            lines.push("");
            lines.push(this.centerText("PAM TECHNO", WIDTH));
            lines.push(this.centerText("Sistem Kasir Digital", WIDTH));
            lines.push(SEPARATOR);
            lines.push("");

            lines.push(
                this.formatRow(
                    "No. Transaksi",
                    receipt.transactionNumber,
                    WIDTH,
                ),
            );
            lines.push(this.formatRow("Tanggal", receipt.date, WIDTH));
            lines.push(this.formatRow("Waktu", receipt.time, WIDTH));
            lines.push(this.formatRow("Kasir", receipt.cashier, WIDTH));
            lines.push(this.formatRow("Jenis", transactionType, WIDTH));
            lines.push("");
            lines.push(SEPARATOR);
            lines.push(this.centerText("DAFTAR BELANJA", WIDTH));
            lines.push(DASH_LINE);

            receipt.items.forEach((item) => {
                const itemTotal = item.qty * item.finalPrice;
                const itemTotalFormatted = "Rp " + this.formatNumber(itemTotal);
                const itemPriceFormatted =
                    "Rp " + this.formatNumber(item.finalPrice);

                const nameLines = this.wrapText(item.name, WIDTH - 2);
                nameLines.forEach((line) => {
                    lines.push(line);
                });

                const qtyPriceStr = `  ${item.qty} x ${itemPriceFormatted}`;
                lines.push(
                    this.formatRow(qtyPriceStr, itemTotalFormatted, WIDTH),
                );
                lines.push("");
            });

            lines.push(DASH_LINE);
            lines.push(
                this.formatRow(
                    "Subtotal",
                    "Rp " + this.formatNumber(receipt.total),
                    WIDTH,
                ),
            );
            lines.push(SEPARATOR);
            lines.push("");

            lines.push(
                this.formatRow("Metode Bayar", paymentMethodText, WIDTH),
            );
            lines.push(
                this.formatRow(
                    "Uang Diterima",
                    "Rp " + this.formatNumber(receipt.amountReceived),
                    WIDTH,
                ),
            );
            lines.push(
                this.formatRow(
                    "Kembalian",
                    "Rp " + this.formatNumber(receipt.change),
                    WIDTH,
                ),
            );
            lines.push("");
            lines.push(SEPARATOR);

            const grandTotalLabel = "GRAND TOTAL";
            const grandTotalValue = "Rp " + this.formatNumber(receipt.total);
            lines.push(this.formatRow(grandTotalLabel, grandTotalValue, WIDTH));
            lines.push(SEPARATOR);
            lines.push("");

            lines.push(this.centerText("Terima kasih atas", WIDTH));
            lines.push(this.centerText("kunjungan Anda!", WIDTH));
            lines.push("");
            lines.push(this.centerText("Barang yang sudah dibeli", WIDTH));
            lines.push(this.centerText("tidak dapat dikembalikan", WIDTH));
            lines.push("");
            lines.push("");

            const receiptText = lines.join("\n");

            const printWindow = window.open(
                "",
                "_blank",
                "width=400,height=600",
            );

            const htmlContent = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Struk - ${receipt.transactionNumber}</title>
    <style>
        @page {
            margin: 2mm;
            size: 58mm auto;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
        body {
            font-family: 'Courier New', 'Lucida Console', Monaco, monospace;
            font-size: 11px;
            line-height: 1.3;
            margin: 0;
            padding: 5mm;
            background: #fff;
            color: #000;
        }
        pre {
            font-family: inherit;
            font-size: inherit;
            line-height: inherit;
            margin: 0;
            padding: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
<pre>${receiptText}</pre>
</body>
</html>`;

            printWindow.document.write(htmlContent);
            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 300);
        },
    }));
});
