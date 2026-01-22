import "./bootstrap";
import Alpine from "alpinejs";
import focus from "@alpinejs/focus";
import { createIcons, icons } from "lucide";

Alpine.plugin(focus);

window.Alpine = Alpine;

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

const debouncedCreateIcons = debounce((options) => {
    createIcons({ icons, ...options });
}, 10);

window.lucide = {
    createIcons: (options = {}) => debouncedCreateIcons(options),
    icons,
};

// Defer Alpine.start() to allow page-specific scripts to register components first
// This fixes "productManager is not defined" errors on the products page
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        Alpine.start();
        createIcons({ icons });
    });
} else {
    // DOM already loaded, start immediately
    Alpine.start();
    createIcons({ icons });
}
