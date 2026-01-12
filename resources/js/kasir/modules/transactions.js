export const transactionsModule = {
    transactionHistory: [],
    receiptData: null,
    showPaymentModal: false,
    showReceiptModal: false,
    showClearCartModal: false,
    showHistoryModal: false,
    mobileCartOpen: false,

    async fetchTransactionHistory() {
        try {
            const response = await fetch("/api/transactions");
            const data = await response.json();
            if (data.success) {
                this.transactionHistory = data.data.map((t) => ({
                    transactionNumber: t.transaction_number,
                    date: new Date(t.created_at).toLocaleDateString("id-ID", {
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                    }),
                    time: new Date(t.created_at).toLocaleTimeString("id-ID", {
                        hour: "2-digit",
                        minute: "2-digit",
                    }),
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

        const amountReceived = parseFloat(paymentModalData.amountReceived || 0);
        const change = amountReceived - this.cartTotal;

        if (change < 0) {
            this.addNotification("Jumlah uang yang diterima kurang dari total");
            return;
        }

        if (this.cart.length === 0) {
            this.addNotification("Keranjang masih kosong");
            return;
        }

        const transactionData = {
            payment_type: this.paymentType,
            payment_method: paymentModalData.selectedPaymentMethod || "tunai",
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
                throw new Error(data.message || "Gagal menyimpan transaksi");
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
            this.addNotification(error.message || "Gagal menyimpan transaksi");
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
};
