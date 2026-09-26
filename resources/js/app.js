import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

// Expose Alpine globally
window.Alpine = Alpine;

// Helper to initialize or re-initialize Lucide icons
const initLucide = (options = {}) => {
    try {
        createIcons({
            icons,
            ...options
        });
    } catch (e) {
        console.error('Failed to initialize Lucide icons:', e);
    }
};

// Expose Lucide on window matching previous CDN behavior
window.lucide = {
    createIcons: initLucide,
    icons,
};

// Start Alpine
Alpine.start();

// Auto-run Lucide on initial page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initLucide());
} else {
    initLucide();
}
