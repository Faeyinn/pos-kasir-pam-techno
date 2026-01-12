export const printModule = {
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

        const printWindow = window.open("", "_blank", "width=300,height=600");
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
                    <div class="row"><span>No. Transaksi</span><span class="bold">${
                        receipt.transactionNumber
                    }</span></div>
                    <div class="row"><span>Tanggal</span><span>${
                        receipt.date
                    }</span></div>
                    <div class="row"><span>Waktu</span><span>${
                        receipt.time
                    }</span></div>
                    <div class="row"><span>Kasir</span><span>${
                        receipt.cashier
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
};
