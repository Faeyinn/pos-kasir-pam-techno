document.addEventListener("alpine:init", () => {
    Alpine.data("kasirSystem", () => ({
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
        isCartExpanded: false,

        // Toggle Cart Expansion
        toggleCartExpansion() {
            this.isCartExpanded = !this.isCartExpanded;
            if (this.isCartExpanded) {
                this.viewMode = 'list';
            }
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

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
                    this.availableTags = (data.data || []).map((t) => ({
                        id: t.id_tag ?? t.id,
                        name: t.nama_tag ?? t.name,
                        color: t.color,
                    }));
                }
            } catch (error) {
                console.error("Error fetching tags:", error);
            }
        },

        normalizeDiscount(discount) {
            if (!discount) return null;

            const rawType = discount.tipe_diskon ?? discount.type;
            const rawValue = discount.nilai_diskon ?? discount.value;

            return {
                id: discount.id_diskon ?? discount.id,
                name: discount.nama_diskon ?? discount.name,
                type:
                    rawType === "persen" || rawType === "percentage"
                        ? "percentage"
                        : "fixed",
                value: Number(rawValue || 0),
            };
        },

        applyDiscount(price, discount) {
            const base = Number(price || 0);
            if (!discount) return base;

            if (discount.type === "percentage") {
                return Math.max(
                    0,
                    Math.round(base - (base * discount.value) / 100),
                );
            }

            return Math.max(0, base - Math.min(base, discount.value));
        },

        async fetchProducts() {
            this.loading = true;
            try {
                const response = await fetch("/api/products");
                const data = await response.json();
                if (data.success) {
                    this.products = (data.data || []).map((p) => {
                        const units = (p.satuan || [])
                            .filter((u) => u.is_active)
                            .map((u) => ({
                                id: u.id_satuan,
                                name: u.nama_satuan,
                                barcode: u.barcode,
                                qtyPerUnit: Number(u.jumlah_per_satuan || 1),
                                price: Number(u.harga_jual || 0),
                                isDefault: !!u.is_default,
                            }));

                        const defaultUnit =
                            units.find((u) => u.isDefault) || units[0] || null;
                        const basePrice = defaultUnit ? defaultUnit.price : 0;

                        const discount = this.normalizeDiscount(p.discount);
                        const discountedBasePrice = discount
                            ? this.applyDiscount(basePrice, discount)
                            : basePrice;

                        // For the product card/list: show one best "grosir" hint if any unit has qtyPerUnit > 1
                        const wholesaleCandidate = units
                            .filter((u) => u.qtyPerUnit > 1)
                            .sort(
                                (a, b) =>
                                    a.price / a.qtyPerUnit -
                                    b.price / b.qtyPerUnit,
                            )[0];

                        const mappedTags = (p.tags || []).map((t) => ({
                            id: t.id_tag ?? t.id,
                            name: t.nama_tag ?? t.name,
                            color: t.color,
                        }));

                        return {
                            id: p.id_produk ?? p.id,
                            name: p.nama_produk ?? p.name,
                            image: p.gambar ?? p.image,
                            stock: Number(p.stok ?? p.stock ?? 0),
                            tags: mappedTags,

                            units,
                            defaultUnitId: defaultUnit ? defaultUnit.id : null,
                            baseUnitId: (units.find(u => u.qtyPerUnit === 1) || units.sort((a, b) => a.qtyPerUnit - b.qtyPerUnit)[0])?.id,

                            // Keep existing UI expectations
                            price: basePrice,
                            originalPrice: basePrice,
                            discount,
                            hasDiscount: !!discount,
                            discountedPrice: discountedBasePrice,

                            wholesale:
                                wholesaleCandidate && wholesaleCandidate.price
                                    ? wholesaleCandidate.price
                                    : 0,
                            wholesaleUnit: wholesaleCandidate
                                ? wholesaleCandidate.name
                                : null,
                            wholesaleQtyPerUnit: wholesaleCandidate
                                ? wholesaleCandidate.qtyPerUnit
                                : 0,
                            wholesalePricePerPiece: wholesaleCandidate
                                ? wholesaleCandidate.price /
                                wholesaleCandidate.qtyPerUnit
                                : basePrice,
                        };
                    });
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

            // 1. Search for specific unit match by barcode
            let matchedProduct = null;
            let matchedUnitId = null;

            for (const p of this.products) {
                const unit = p.units.find(u => u.barcode === code);
                if (unit) {
                    matchedProduct = p;
                    matchedUnitId = unit.id;
                    break;
                }
            }

            // 2. Fallback: Search by ID or Name (old logic)
            if (!matchedProduct) {
                matchedProduct = this.products.find(
                    (p) => p.id == code || p.name.toLowerCase() === code.toLowerCase()
                );
            }

            if (matchedProduct) {
                this.addToCart(matchedProduct, matchedUnitId);
                this.addNotification(
                    `Produk ditambahkan: ${matchedProduct.name}`,
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


        syncPaymentType() {
            this.paymentType = this.isWholesalePresent() ? "wholesale" : "retail";
        },

        isWholesalePresent() {
            return this.cart.some(item => this.isWholesale(item));
        },

        addToCart(product, forcedUnitId = null) {
            const existingItem = this.cart.find(
                (item) => item.id === product.id,
            );

            const defaultUnitId = forcedUnitId ||
                product.defaultUnitId ||
                (product.units && product.units[0]
                    ? product.units[0].id
                    : null);

            if (existingItem) {
                // If specific unit is forced (from barcode), find or add that selection
                if (forcedUnitId) {
                    const selectionIndex = existingItem.selections.findIndex(s => s.unitId === forcedUnitId);
                    if (selectionIndex !== -1) {
                        this.updateQty(product.id, 1, selectionIndex);
                    } else {
                        // Check stock for adding new unit selection
                        const unitObj = product.units.find(u => u.id === forcedUnitId);
                        if (this.getItemRequiredStock(existingItem) + (unitObj ? unitObj.qtyPerUnit : 1) > existingItem.stock) {
                            this.addNotification(`Stok tidak mencukupi untuk menambah satuan baru.`);
                            return;
                        }
                        existingItem.selections.push({ unitId: forcedUnitId, qty: 1 });
                    }
                } else {
                    // Default behavior: increment first selection
                    this.updateQty(product.id, 1, 0);
                }
            } else {
                const initialItem = {
                    ...product,
                    selections: [
                        { unitId: defaultUnitId, qty: 1 }
                    ],
                };

                // Check stock for initial item
                if (this.getItemRequiredStock(initialItem) > product.stock) {
                    this.addNotification(
                        `Stok tidak mencukupi! Sisa stok untuk ${product.name} adalah ${product.stock} unit.`,
                    );
                    return;
                }

                this.cart.push(initialItem);
            }

            this.syncPaymentType();
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        addUnitSelection(productId) {
            const item = this.cart.find(i => i.id === productId);
            if (!item) return;

            // Find a unit that hasn't been selected yet, or just use the base unit
            const usedUnitIds = item.selections.map(s => s.unitId);
            const nextUnit = item.units.find(u => !usedUnitIds.includes(u.id)) || item.units[0];

            if (this.getItemRequiredStock(item) + (nextUnit ? nextUnit.qtyPerUnit : 1) > item.stock) {
                this.addNotification(`Stok tidak mencukupi untuk menambah satuan baru.`);
                return;
            }

            item.selections.push({
                unitId: nextUnit ? nextUnit.id : item.defaultUnitId,
                qty: 1
            });

            this.syncPaymentType();
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        removeUnitSelection(productId, index) {
            const item = this.cart.find(i => i.id === productId);
            if (!item) return;

            if (item.selections.length > 1) {
                item.selections.splice(index, 1);
            } else {
                this.removeFromCart(productId);
            }

            this.syncPaymentType();
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        updateQty(productId, delta, index = 0) {
            const item = this.cart.find((i) => i.id === productId);
            if (item && item.selections[index]) {
                const selection = item.selections[index];
                const nextQty = selection.qty + delta;

                if (delta > 0) {
                    const unit = item.units.find(u => u.id === selection.unitId);
                    const qtyPerUnit = unit ? unit.qtyPerUnit : 1;
                    // Check total stock for all selections
                    const totalRequired = this.getItemRequiredStock(item) + (delta * qtyPerUnit);

                    if (totalRequired > item.stock) {
                        this.addNotification(
                            `Stok tidak mencukupi! Sisa stok untuk ${item.name} adalah ${item.stock} unit.`,
                        );
                        return;
                    }
                }

                selection.qty = nextQty;

                if (selection.qty <= 0) {
                    this.removeUnitSelection(productId, index);
                    return;
                }

                this.syncPaymentType();
            }
        },

        setItemUnit(productId, newUnitId, index = 0) {
            const item = this.cart.find((i) => i.id === productId);
            if (!item || !item.selections[index]) return;

            newUnitId = Number(newUnitId);
            const selection = item.selections[index];

            // Stock check for unit change
            const oldUnit = item.units.find(u => u.id === selection.unitId);
            const newUnit = item.units.find(u => u.id === newUnitId);

            const oldBaseQty = selection.qty * (oldUnit ? oldUnit.qtyPerUnit : 1);
            const newBaseQty = selection.qty * (newUnit ? newUnit.qtyPerUnit : 1);

            if (this.getItemRequiredStock(item) - oldBaseQty + newBaseQty > item.stock) {
                this.addNotification(`Stok tidak mencukupi untuk berpindah ke satuan ${newUnit ? newUnit.name : ''}.`);
                return;
            }

            selection.unitId = newUnitId;

            this.syncPaymentType();
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        removeFromCart(productId) {
            this.cart = this.cart.filter((item) => item.id !== productId);
            this.syncPaymentType();
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        clearCart() {
            this.showClearCartModal = true;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },

        // Helper to get selected unit object for a specific selection index
        getSelectedUnitForSelection(item, index) {
            const selection = item.selections[index];
            if (!selection) return null;
            return item.units.find(u => u.id === selection.unitId) || item.units[0];
        },

        // Overriding old helpers for backward compatibility where possible
        getSelectedUnit(item) {
            return this.getSelectedUnitForSelection(item, 0);
        },

        getItemRequiredStock(item) {
            return item.selections.reduce((total, s) => {
                const unit = item.units.find(u => u.id === s.unitId);
                return total + (s.qty * (unit ? unit.qtyPerUnit : 1));
            }, 0);
        },

        getItemPrice(item, index = 0) {
            const unit = this.getSelectedUnitForSelection(item, index);
            const unitPrice = unit ? unit.price : item.price;
            if (item.discount) {
                return this.applyDiscount(unitPrice, item.discount);
            }
            return unitPrice;
        },

        getItemTotalPrice(item) {
            return item.selections.reduce((total, s, index) => {
                return total + (this.getItemPrice(item, index) * s.qty);
            }, 0);
        },

        get cartTotal() {
            return this.cart.reduce(
                (total, item) => total + this.getItemTotalPrice(item),
                0,
            );
        },

        // Total item dalam satuan dasar (pcs) untuk seluruh keranjang
        get cartTotalQtyDasar() {
            return this.cart.reduce((total, item) => {
                return total + this.getItemRequiredStock(item);
            }, 0);
        },

        get canApplyWholesale() {
            return this.cart.some((item) =>
                item.selections.some(s => {
                    const unit = item.units.find(u => u.id === s.unitId);
                    return unit && unit.qtyPerUnit > 1;
                })
            );
        },

        isWholesale(item) {
            return item.selections.some(s => {
                const unit = item.units.find(u => u.id === s.unitId);
                return unit && unit.qtyPerUnit > 1;
            });
        },

        // ==================== TRANSACTIONS ====================
        async fetchTransactionHistory() {
            try {
                const response = await fetch("/api/transactions");
                const data = await response.json();
                if (data.success) {
                    this.transactionHistory = data.data.map((t) => {
                        const items = (t.items || []).map((item) => ({
                            id_produk: item.id_produk,
                            name: item.nama_produk,
                            qty: item.jumlah,
                            finalPrice: item.harga_jual,
                            unitName: item.nama_satuan,
                            qtyPerUnit: item.jumlah_per_satuan,
                        }));

                        return {
                            transactionNumber: t.nomor_transaksi,
                            date: new Date(t.created_at).toLocaleDateString("id-ID", {
                                day: "2-digit",
                                month: "long",
                                year: "numeric",
                            }),
                            time: new Date(t.created_at).toLocaleTimeString("id-ID", {
                                hour: "2-digit",
                                minute: "2-digit",
                            }),
                            cashier: t.user.nama,
                            paymentMethod: t.metode_pembayaran,
                            amountReceived: t.jumlah_dibayar,
                            change: t.kembalian,
                            items: items,
                            groupedItems: this.groupReceiptItems(items),
                            paymentType: t.jenis_transaksi === "grosir" ? "wholesale" : "retail",
                            subtotal: t.total_belanja,
                            total: t.total_transaksi,
                        };
                    });
                }
            } catch (error) {
                console.error("Error fetching transaction history:", error);
            }
        },

        groupReceiptItems(items) {
            return Object.values(items.reduce((acc, item) => {
                const id = item.id_produk;
                if (!acc[id]) {
                    acc[id] = {
                        name: item.name,
                        totalPrice: 0,
                        totalQtyDasar: 0,
                        parts: [],
                    };
                }
                acc[id].totalPrice += item.qty * item.finalPrice;
                acc[id].totalQtyDasar += item.qty * item.qtyPerUnit;
                acc[id].parts.push(item);
                return acc;
            }, {}));
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

            const itemsForBackend = [];
            this.cart.forEach((item) => {
                item.selections.forEach(s => {
                    itemsForBackend.push({
                        id_produk: item.id,
                        id_satuan: s.unitId,
                        jumlah: s.qty,
                    });
                });
            });

            const transactionData = {
                jenis_transaksi: this.isWholesalePresent() ? "grosir" : "eceran",
                metode_pembayaran:
                    paymentModalData.selectedPaymentMethod || "tunai",
                jumlah_dibayar: Math.round(amountReceived),
                items: itemsForBackend,
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
                const items = (transaction.items || []).map((item) => ({
                    id_produk: item.id_produk,
                    name: item.nama_produk,
                    qty: item.jumlah,
                    finalPrice: item.harga_jual,
                    unitName: item.nama_satuan,
                    qtyPerUnit: item.jumlah_per_satuan,
                }));

                const newReceipt = {
                    transactionNumber: transaction.nomor_transaksi,
                    date: now.toLocaleDateString("id-ID", {
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                    }),
                    time: now.toLocaleTimeString("id-ID", {
                        hour: "2-digit",
                        minute: "2-digit",
                    }),
                    cashier: transaction.user.nama,
                    paymentMethod: transaction.metode_pembayaran,
                    amountReceived: transaction.jumlah_dibayar,
                    change: transaction.kembalian,
                    items: items,
                    groupedItems: this.groupReceiptItems(items),
                    paymentType: transaction.jenis_transaksi === "grosir" ? "wholesale" : "retail",
                    subtotal: transaction.total_belanja,
                    total: transaction.total_transaksi,
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
            lines.push(
                this.centerText(
                    "Jl. M. Yunus No. 6, Lubuk Lintah, Kec. Kuranji, Kota Padang",
                    WIDTH,
                ),
            );
            lines.push(this.centerText("Telp: 0895600077007", WIDTH));
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

            const groupedItems = receipt.groupedItems || this.groupReceiptItems(receipt.items);

            groupedItems.forEach((group) => {
                const itemTotalFormatted = "Rp " + this.formatNumber(group.totalPrice);

                // Product Name + Total Price
                const nameLines = this.wrapText(group.name, WIDTH - itemTotalFormatted.length - 1);
                nameLines.forEach((line, idx) => {
                    if (idx === nameLines.length - 1) {
                        lines.push(this.formatRow(line, itemTotalFormatted, WIDTH));
                    } else {
                        lines.push(line);
                    }
                });

                // Unit breakdown
                group.parts.forEach(p => {
                    const subtotalFormatted = "Rp " + this.formatNumber(p.qty * p.finalPrice);
                    const qtyPriceStr = `${p.qty} x ${p.unitName} @ Rp ${this.formatNumber(p.finalPrice)}`;

                    const wrappedPart = this.wrapText(qtyPriceStr, WIDTH - subtotalFormatted.length - 3);
                    wrappedPart.forEach((line, idx) => {
                        if (idx === wrappedPart.length - 1) {
                            lines.push(this.formatRow("  " + line, subtotalFormatted, WIDTH));
                        } else {
                            lines.push("  " + line);
                        }
                    });
                });
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

            // Create Blob and download as .txt
            const blob = new Blob([receiptText], {
                type: "text/plain;charset=utf-8",
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `struk-${receipt.transactionNumber}.txt`;
            document.body.appendChild(a);
            a.click();

            // Cleanup
            setTimeout(() => {
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            }, 100);
        },
    }));
});
