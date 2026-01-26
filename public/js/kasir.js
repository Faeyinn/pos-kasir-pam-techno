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
        getSelectedUnit(item) {
            if (!item.units || item.units.length === 0) return null;
            return (
                item.units.find((u) => u.id === item.selectedUnitId) ||
                item.units.find((u) => u.isDefault) ||
                item.units[0]
            );
        },

        getItemRequiredStock(item) {
            const unit = this.getSelectedUnit(item);
            const qtyPerUnit = unit ? unit.qtyPerUnit : 1;
            return (Number(item.qty || 0) || 0) * qtyPerUnit;
        },

        syncPaymentType() {
            this.paymentType = this.cart.some((item) => this.isWholesale(item))
                ? "wholesale"
                : "retail";
        },

        addToCart(product) {
            const existingItem = this.cart.find(
                (item) => item.id === product.id,
            );
            const requestQty = 1;

            const defaultUnitId =
                product.defaultUnitId ||
                (product.units && product.units[0]
                    ? product.units[0].id
                    : null);

            if (existingItem) {
                const nextQty = existingItem.qty + requestQty;
                const unit = this.getSelectedUnit(existingItem);
                const qtyPerUnit = unit ? unit.qtyPerUnit : 1;
                const requiredStock = nextQty * qtyPerUnit;

                if (requiredStock > existingItem.stock) {
                    this.addNotification(
                        `Stok tidak mencukupi! Sisa stok untuk ${product.name} adalah ${existingItem.stock} unit.`,
                    );
                    return;
                }

                existingItem.qty = nextQty;
            } else {
                const initialItem = {
                    ...product,
                    qty: requestQty,
                    selectedUnitId: defaultUnitId,
                };

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

        updateQty(productId, delta) {
            const item = this.cart.find((i) => i.id === productId);
            if (item) {
                const nextQty = item.qty + delta;
                if (delta > 0) {
                    const unit = this.getSelectedUnit(item);
                    const qtyPerUnit = unit ? unit.qtyPerUnit : 1;
                    const requiredStock = nextQty * qtyPerUnit;
                    if (requiredStock > item.stock) {
                        this.addNotification(
                            `Stok tidak mencukupi! Sisa stok untuk ${item.name} adalah ${item.stock} unit.`,
                        );
                        return;
                    }
                }

                item.qty = nextQty;

                if (item.qty <= 0) {
                    this.removeFromCart(productId);
                    return;
                }

                this.syncPaymentType();
            }
        },

        setItemUnit(productId, newUnitId) {
            const item = this.cart.find((i) => i.id === productId);
            if (!item) return;

            // Pastikan format ID sesuai (number)
            newUnitId = Number(newUnitId);

            // 1. Ambil Data Unit Lama (Sebelum berubah) untuk hitung stok dasar
            // Karena kita menghapus x-model, item.selectedUnitId saat ini masih ID lama
            const oldUnit = this.getSelectedUnit(item);
            const oldQtyPerUnit = oldUnit ? Number(oldUnit.qtyPerUnit || 1) : 1;

            // 2. Hitung Total Kuantitas dalam Satuan Dasar
            // Contoh: 2 Lusin (per 12) = 24 Pcs (dasar)
            const currentBaseQty = item.qty * oldQtyPerUnit;

            // 3. Set Unit Baru
            item.selectedUnitId = newUnitId;
            const newUnit = this.getSelectedUnit(item);
            const newQtyPerUnit = newUnit ? Number(newUnit.qtyPerUnit || 1) : 1;

            // 4. Hitung Kuantitas Baru
            // Contoh: 24 Pcs dijadikan Dus (per 24) = 1 Dus
            // Contoh: 1 Pcs dijadikan Dus (per 40) = 0.025 Dus -> Dibulatkan ke 1 Dus (UX Enhancement)
            let rawNewQty = currentBaseQty / newQtyPerUnit;

            // Fitur User-Friendly:
            // - Jika hasil konversi < 1 (misal 1 Pcs diubah ke Dus), otomatis set jadi 1 Dus.
            // - Jika hasil konversi >= 1 (misal 40 Pcs diubah ke Dus), biarkan hasil konversinya.
            if (rawNewQty < 1) {
                this.addNotification(
                    `Kuantitas disesuaikan ke minimal 1 ${newUnit ? newUnit.name : ""}`,
                    "info",
                );
                item.qty = 1;
            } else {
                // Pembulatan presisi desimal (max 4 angka belakang koma)
                item.qty = Math.round(rawNewQty * 10000) / 10000;
            }

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

        getItemPrice(item) {
            const unit = this.getSelectedUnit(item);
            const unitPrice = unit ? unit.price : item.price;

            if (item.discount) {
                return this.applyDiscount(unitPrice, item.discount);
            }

            return unitPrice;
        },

        get cartTotal() {
            return this.cart.reduce(
                (total, item) => total + this.getItemPrice(item) * item.qty,
                0,
            );
        },

        // Total item dalam satuan dasar (pcs) untuk seluruh keranjang
        get cartTotalQtyDasar() {
            return this.cart.reduce((total, item) => {
                const unit = this.getSelectedUnit(item);
                const qtyPerUnit = unit ? Number(unit.qtyPerUnit || 1) : 1;
                return total + (Number(item.qty || 0) || 0) * qtyPerUnit;
            }, 0);
        },

        get canApplyWholesale() {
            return this.cart.some((item) => item.wholesale > 0);
        },

        isWholesale(item) {
            const unit = this.getSelectedUnit(item);
            return !!unit && Number(unit.qtyPerUnit || 1) > 1;
        },

        // ==================== TRANSACTIONS ====================
        async fetchTransactionHistory() {
            try {
                const response = await fetch("/api/transactions");
                const data = await response.json();
                if (data.success) {
                    this.transactionHistory = data.data.map((t) => ({
                        transactionNumber: t.nomor_transaksi,
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
                        cashier: t.user.nama,
                        paymentMethod: t.metode_pembayaran,
                        amountReceived: t.jumlah_dibayar,
                        change: t.kembalian,
                        items: t.items.map((item) => ({
                            name: item.nama_produk,
                            qty: item.jumlah,
                            finalPrice: item.harga_jual,
                        })),
                        paymentType:
                            t.jenis_transaksi === "grosir"
                                ? "wholesale"
                                : "retail",
                        subtotal: t.total_belanja,
                        total: t.total_transaksi,
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

            const transactionData = {
                jenis_transaksi: this.cart.some((item) =>
                    this.isWholesale(item),
                )
                    ? "grosir"
                    : "eceran",
                metode_pembayaran:
                    paymentModalData.selectedPaymentMethod || "tunai",
                jumlah_dibayar: Math.round(amountReceived),
                items: this.cart.map((item) => ({
                    id_produk: item.id,
                    id_satuan: item.selectedUnitId,
                    jumlah: item.qty,
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
                    items: (transaction.items || []).map((item) => ({
                        name: item.nama_produk,
                        qty: item.jumlah,
                        finalPrice: item.harga_jual,
                    })),
                    paymentType:
                        transaction.jenis_transaksi === "grosir"
                            ? "wholesale"
                            : "retail",
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
