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

    <!-- Preconnect to CDNs for fast DNS & TLS negotiation -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://unpkg.com">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js (deferred) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons (deferred) -->
    <script defer src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="h-full font-sans antialiased overflow-hidden flex bg-slate-950" x-data="{ sidebarOpen: false }">

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
                    <h1 class="text-lg font-bold text-white tracking-tight">@yield('page_title', 'Dashboard')</h1>
                    <p class="text-xs text-slate-400">@yield('page_subtitle', 'Manage store inventory, banners, orders, and vendors')</p>
                </div>
            </div>

            <!-- Topbar Quick Actions -->
            <div class="flex items-center gap-3">
                <a href="http://localhost:3000" target="_blank" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/60 hover:text-white transition-all shadow-sm">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-brand-400"></i>
                    <span>Live Next.js Store</span>
                </a>

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
        <main class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6">
            
            <!-- Toast Notifications -->
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="flex items-center justify-between p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 shadow-lg backdrop-blur-md">
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
                 class="flex items-center justify-between p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 shadow-lg backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-rose-400 hover:text-rose-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            @endif

            @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300">
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

            <!-- Dynamic View Content -->
            @yield('content')
        </main>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
