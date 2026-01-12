import { notificationsModule } from "./modules/notifications.js";
import { productsModule } from "./modules/products.js";
import { cartModule } from "./modules/cart.js";
import { transactionsModule } from "./modules/transactions.js";
import { printModule } from "./modules/print.js";

document.addEventListener("alpine:init", () => {
    Alpine.data("kasirSystem", () => ({
        ...notificationsModule,
        ...productsModule,
        ...cartModule,
        ...transactionsModule,
        ...printModule,

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
    }));
});
