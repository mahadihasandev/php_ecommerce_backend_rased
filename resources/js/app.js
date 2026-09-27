import Alpine from 'alpinejs';
import {
    createIcons,
    AlertCircle,
    AlertTriangle,
    ArrowRight,
    Award,
    Box,
    Check,
    CheckCircle,
    DollarSign,
    Edit3,
    ExternalLink,
    Flame,
    Folder,
    FolderPlus,
    FolderTree,
    Image,
    ImagePlus,
    Info,
    LayoutDashboard,
    Link,
    Lock,
    LogIn,
    LogOut,
    Mail,
    Menu,
    Package,
    Plus,
    PlusCircle,
    Search,
    Shield,
    ShieldAlert,
    ShieldCheck,
    ShoppingBag,
    ShoppingCart,
    Sparkles,
    Star,
    Store,
    Trash2,
    TrendingUp,
    Upload,
    UploadCloud,
    UserCheck,
    UserPlus,
    X,
    Zap
} from 'lucide';

// Add new Blade icons here so the bundle only contains icons used by the portal.
const icons = {
    AlertCircle,
    AlertTriangle,
    ArrowRight,
    Award,
    Box,
    Check,
    CheckCircle,
    DollarSign,
    Edit3,
    ExternalLink,
    Flame,
    Folder,
    FolderPlus,
    FolderTree,
    Image,
    ImagePlus,
    Info,
    LayoutDashboard,
    Link,
    Lock,
    LogIn,
    LogOut,
    Mail,
    Menu,
    Package,
    Plus,
    PlusCircle,
    Search,
    Shield,
    ShieldAlert,
    ShieldCheck,
    ShoppingBag,
    ShoppingCart,
    Sparkles,
    Star,
    Store,
    Trash2,
    TrendingUp,
    Upload,
    UploadCloud,
    UserCheck,
    UserPlus,
    X,
    Zap
};

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
