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

Alpine.start();
createIcons({ icons });
