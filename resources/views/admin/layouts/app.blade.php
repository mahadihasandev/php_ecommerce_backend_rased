<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - ShopEcommerce Multi-Vendor</title>

    <!-- Tab Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Preload Self-Hosted Fonts -->
    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans-latin.woff2') }}" as="font" type="font/woff2" crossorigin>

    <!-- Pre-compiled Assets (Tailwind v4 + Alpine.js + Lucide Icons) -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
        <script defer src="{{ asset('js/admin.js') }}"></script>
    @endif

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Skeleton Shimmer Waves */
        @keyframes shimmerWave {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .shimmer {
            position: relative;
            overflow: hidden;
        }
        .shimmer::after {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            transform: translateX(-100%);
            background-image: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0,
                rgba(255, 255, 255, 0.05) 35%,
                rgba(255, 255, 255, 0.16) 50%,
                rgba(255, 255, 255, 0.05) 65%,
                rgba(255, 255, 255, 0) 100%
            );
            animation: shimmerWave 1.6s infinite ease-in-out;
            content: "";
        }
        .animate-fade-in {
            animation: fadeIn 0.2s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="h-full font-sans antialiased overflow-hidden flex bg-slate-950" x-data="{ sidebarOpen: false }">

    <!-- Top Loading Progress Bar -->
    <div id="admin-top-progress" class="fixed top-0 left-0 h-1 bg-gradient-to-r from-brand-500 via-indigo-400 to-purple-500 z-[9999] transition-all duration-300 pointer-events-none opacity-0 shadow-lg shadow-brand-500/50" style="width: 0%;"></div>

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 lg:hidden" 
         x-cloak>
    </div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900/95 border-r border-slate-800/80 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 backdrop-blur-xl">
        
        <!-- Logo Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800/70">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-violet-400 flex items-center justify-center shadow-lg shadow-brand-500/25 group-hover:scale-105 transition-transform duration-300">
                    <i data-lucide="shopping-bag" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <span class="text-lg font-bold bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">ShopEcommerce</span>
                    <span class="block text-[11px] font-medium tracking-wider uppercase text-brand-400">Portal & Multi-Vendor</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
            <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Main Menu</div>

            <!-- Overview -->
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Dashboard Overview</span>
            </a>

            <!-- Products -->
            @if(auth()->user()->hasPermission('manage_products'))
            <a href="{{ route('admin.products.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('admin.products.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>Products Manager</span>
            </a>
            @endif

            <!-- Banners -->
            @if(auth()->user()->hasPermission('manage_banners'))
            <a href="{{ route('admin.banners.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('admin.banners.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="image" class="w-4 h-4"></i>
                <span>Banners & Promotions</span>
            </a>
            @endif

            <!-- Orders -->
            @if(auth()->user()->hasPermission('manage_orders'))
            <a href="{{ route('admin.orders.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('admin.orders.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span>Customer Orders</span>
            </a>
            @endif

            <div class="pt-5 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Catalog & Store</div>

            <!-- Categories -->
            @if(auth()->user()->hasPermission('manage_categories'))
            <a href="{{ route('admin.categories.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="folder-tree" class="w-4 h-4"></i>
                <span>Categories</span>
            </a>
            @endif

            <!-- Brands -->
            @if(auth()->user()->hasPermission('manage_brands'))
            <a href="{{ route('admin.brands.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('admin.brands.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="award" class="w-4 h-4"></i>
                <span>Brands</span>
            </a>
            @endif

            <!-- Users & Permissions (Super Admin or manage_users) -->
            @if(auth()->user()->hasPermission('manage_users'))
            <div class="pt-5 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Access & Multi-Vendor</div>
            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>Users & Permissions</span>
            </a>
            @endif
        </div>

        <!-- User Profile Footer Card -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/60">
            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-800/40 border border-slate-700/50">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                            {{ auth()->user()->role === 'admin' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : (auth()->user()->role === 'vendor' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30') }}">
                            {{ auth()->user()->role }}
                        </span>
                        @if(auth()->user()->store_name)
                            <span class="text-[11px] text-slate-400 truncate">{{ auth()->user()->store_name }}</span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sign out" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-700/50 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-950">
        
        <!-- Top Navbar -->
        <header class="h-20 bg-slate-900/60 border-b border-slate-800/80 backdrop-blur-xl flex items-center justify-between px-6 lg:px-8 z-30">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-white p-2 rounded-lg bg-slate-800/60 border border-slate-700/50">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="hidden sm:block">
                    <h1 id="navbar-page-title" class="text-lg font-bold text-white tracking-tight">@yield('page_title', 'Dashboard')</h1>
                    <p id="navbar-page-subtitle" class="text-xs text-slate-400">@yield('page_subtitle', 'Manage store inventory, banners, orders, and vendors')</p>
                </div>
            </div>

            <!-- Topbar Quick Actions -->
            <div class="flex items-center gap-3">
                <a href="http://localhost:3000" target="_blank" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 hover:text-white transition-all shadow-sm">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-brand-400"></i>
                    <span class="hidden md:inline">Live Next.js Store</span>
                </a>

                <form action="{{ route('admin.purge-cache') }}" method="POST" class="inline" onsubmit="window.AdminNav && window.AdminNav.startProgress()">
                    @csrf
                    <button type="submit" title="Flush Redis / App Cache" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 hover:border-amber-500/50 transition-all shadow-sm cursor-pointer">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span class="hidden md:inline">Flush Cache</span>
                    </button>
                </form>

                @if(auth()->user()->hasPermission('manage_products'))
                <a href="{{ route('admin.products.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-500/25 transition-all">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Add Product</span>
                </a>
                @endif
            </div>
        </header>

        <!-- Main Body Scroll Container -->
        <main id="admin-main-scroll" class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6">
            
            <!-- Toast Notifications -->
            <div id="admin-toasts">
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                     class="flex items-center justify-between p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 shadow-lg backdrop-blur-md mb-4">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-400 hover:text-emerald-200">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)" 
                     class="flex items-center justify-between p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 shadow-lg backdrop-blur-md mb-4">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-400 hover:text-rose-200">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                @endif

                @if(isset($errors) && $errors->any())
                <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 mb-4">
                    <div class="flex items-center gap-2 font-semibold text-sm mb-1">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-400"></i>
                        <span>Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 text-rose-300/90 pl-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <!-- Dynamic View Content Container -->
            <div id="admin-content-real" class="animate-fade-in">
                @yield('content')
            </div>

            <!-- Instant Skeleton Loading Overlay (Shown during navigation) -->
            <div id="admin-skeleton-overlay" class="hidden">
                @include('admin.components.skeleton')
            </div>
        </main>
    </div>

    <!-- Initialize Lucide Icons & Instant Navigation Engine -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });

        // Instant Navigation & Shimmer Skeleton Engine
        window.AdminNav = (function() {
            const progressBar = document.getElementById('admin-top-progress');
            const contentReal = document.getElementById('admin-content-real');
            const skeletonOverlay = document.getElementById('admin-skeleton-overlay');
            const mainScroll = document.getElementById('admin-main-scroll');
            let progressInterval = null;

            function startProgress() {
                if (!progressBar) return;
                clearInterval(progressInterval);
                progressBar.style.transition = 'width 0.25s ease-out, opacity 0.2s ease-in';
                progressBar.style.opacity = '1';
                progressBar.style.width = '35%';

                let currentWidth = 35;
                progressInterval = setInterval(() => {
                    if (currentWidth < 85) {
                        currentWidth += Math.random() * 15;
                        progressBar.style.width = Math.min(currentWidth, 88) + '%';
                    }
                }, 180);
            }

            function completeProgress() {
                if (!progressBar) return;
                clearInterval(progressInterval);
                progressBar.style.transition = 'width 0.15s ease-in, opacity 0.25s ease-out';
                progressBar.style.width = '100%';
                setTimeout(() => {
                    progressBar.style.opacity = '0';
                    setTimeout(() => {
                        progressBar.style.width = '0%';
                    }, 250);
                }, 150);
            }

            function showSkeleton(targetUrl) {
                if (!skeletonOverlay || !contentReal) return;
                
                const urlStr = (targetUrl || '').toLowerCase();
                const dView = document.getElementById('skeleton-dashboard-view');
                const tView = document.getElementById('skeleton-table-view');
                const fView = document.getElementById('skeleton-form-view');
                const sText = document.getElementById('skeleton-status-text');

                if (urlStr.includes('/create') || urlStr.includes('/edit')) {
                    if (dView) dView.classList.add('hidden');
                    if (tView) tView.classList.add('hidden');
                    if (fView) fView.classList.remove('hidden');
                    if (sText) sText.textContent = 'Preparing form editor & component assets...';
                } else if (urlStr.endsWith('/admin') || urlStr.endsWith('/admin/')) {
                    if (dView) dView.classList.remove('hidden');
                    if (tView) tView.classList.add('hidden');
                    if (fView) fView.classList.add('hidden');
                    if (sText) sText.textContent = 'Loading live store analytics & catalog metrics...';
                } else {
                    if (dView) dView.classList.add('hidden');
                    if (tView) tView.classList.remove('hidden');
                    if (fView) fView.classList.add('hidden');
                    if (sText) sText.textContent = 'Fetching latest catalog records & status data...';
                }

                contentReal.classList.add('hidden');
                skeletonOverlay.classList.remove('hidden');
            }

            function hideSkeleton() {
                if (!skeletonOverlay || !contentReal) return;
                skeletonOverlay.classList.add('hidden');
                contentReal.classList.remove('hidden');
            }

            async function navigateTo(url, pushState = true) {
                startProgress();
                showSkeleton(url);

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    // If redirected or non-200, fallback to native navigation
                    if (response.redirected || !response.ok) {
                        window.location.href = url;
                        return;
                    }

                    const htmlText = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(htmlText, 'text/html');

                    const newContent = doc.getElementById('admin-content-real');
                    if (!newContent) {
                        window.location.href = url;
                        return;
                    }

                    // Update Document Title
                    if (doc.title) {
                        document.title = doc.title;
                    }

                    // Update Navbar Page Title & Subtitle
                    const newPageTitle = doc.getElementById('navbar-page-title');
                    const curPageTitle = document.getElementById('navbar-page-title');
                    if (newPageTitle && curPageTitle) {
                        curPageTitle.textContent = newPageTitle.textContent;
                    }

                    const newPageSubtitle = doc.getElementById('navbar-page-subtitle');
                    const curPageSubtitle = document.getElementById('navbar-page-subtitle');
                    if (newPageSubtitle && curPageSubtitle) {
                        curPageSubtitle.textContent = newPageSubtitle.textContent;
                    }

                    // Update Toasts
                    const newToasts = doc.getElementById('admin-toasts');
                    const curToasts = document.getElementById('admin-toasts');
                    if (newToasts && curToasts) {
                        curToasts.innerHTML = newToasts.innerHTML;
                    }

                    // Swap Main Content
                    contentReal.innerHTML = newContent.innerHTML;

                    // Update Sidebar Active Highlight
                    const currentPath = new URL(url, window.location.origin).pathname.replace(/\/+$/, '');
                    document.querySelectorAll('aside a[href]').forEach(a => {
                        const aPath = new URL(a.href, window.location.origin).pathname.replace(/\/+$/, '');
                        const isActive = (aPath === currentPath) || (aPath !== '/admin' && currentPath.startsWith(aPath));

                        if (isActive) {
                            a.className = a.className
                                .replace('text-slate-400 hover:text-white hover:bg-slate-800/60', '')
                                .trim() + ' bg-brand-600 text-white shadow-lg shadow-brand-600/30 font-semibold';
                        } else {
                            a.className = a.className
                                .replace('bg-brand-600 text-white shadow-lg shadow-brand-600/30 font-semibold', '')
                                .trim() + ' text-slate-400 hover:text-white hover:bg-slate-800/60';
                        }
                    });

                    // Execute only page-specific scripts (e.g. from @stack('scripts') or content)
                    doc.body.querySelectorAll('script').forEach(oldScript => {
                        const scriptText = oldScript.textContent.trim();
                        // Skip global layout scripts
                        if (!scriptText || scriptText.includes('AdminNav') || scriptText.includes('_origWarn') || scriptText.includes('lucide.createIcons')) {
                            return;
                        }
                        try {
                            const newScript = document.createElement('script');
                            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                            newScript.textContent = scriptText;
                            document.body.appendChild(newScript);
                        } catch(e) {
                            console.warn('Page script execution error:', e);
                        }
                    });

                    // Re-init Lucide Icons
                    if (window.lucide) {
                        lucide.createIcons();
                    }

                    // Re-init Alpine tree on new content if available
                    if (window.Alpine && window.Alpine.initTree) {
                        try {
                            window.Alpine.initTree(contentReal);
                        } catch(e) {}
                    }

                    // Update Browser URL History
                    if (pushState) {
                        history.pushState({ url }, '', url);
                    }

                    // Scroll to top smoothly
                    if (mainScroll) {
                        mainScroll.scrollTo({ top: 0, behavior: 'instant' });
                    }

                    hideSkeleton();
                    completeProgress();
                } catch(err) {
                    console.warn('Instant navigation fallback:', err);
                    window.location.href = url;
                }
            }

            // Intercept internal admin clicks
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link || !link.href) return;

                // Ignore clicks with modifiers (new tab / window)
                if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
                if (link.target === '_blank' || link.hasAttribute('download')) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

                const targetUrl = new URL(link.href, window.location.origin);
                if (targetUrl.origin !== window.location.origin) return;
                if (!targetUrl.pathname.startsWith('/admin')) return;
                if (targetUrl.pathname.includes('/logout')) return;

                e.preventDefault();
                if (targetUrl.href === window.location.href) return;

                navigateTo(targetUrl.href, true);
            });

            // Handle Browser Back / Forward buttons
            window.addEventListener('popstate', function(e) {
                navigateTo(window.location.href, false);
            });

            // Progress bar feedback on beforeunload (form submits, etc.)
            window.addEventListener('beforeunload', function() {
                startProgress();
            });

            return {
                startProgress,
                completeProgress,
                showSkeleton,
                hideSkeleton,
                navigateTo
            };
        })();
    </script>
    @stack('scripts')
</body>
</html>
