@extends('admin.layouts.app')

@section('title', 'Dashboard Overview')
@section('page_title', 'Marketplace & Store Overview')
@section('page_subtitle', 'Live analytics, inventory stock alerts, and fulfillment tracking')

@section('content')
<div class="space-y-8">

    <!-- Hero / Welcome Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-700 via-indigo-700 to-purple-800 p-8 shadow-2xl border border-indigo-500/20">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-indigo-100 backdrop-blur-md mb-3 border border-white/15">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300"></i>
                Multi-Vendor Commerce Engine
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Welcome back, {{ auth()->user()->name }}!
            </h2>
            <p class="text-sm text-indigo-100/90 mt-2 leading-relaxed">
                @if($isVendor)
                    Manage your merchant catalog, track customer purchases for your items, and grow your sales.
                @else
                    You have full administrative oversight across all vendors, categories, orders, and promotional banners.
                @endif
            </p>
        </div>
        
        <!-- Decorative Glow -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Revenue -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg backdrop-blur-sm hover:border-slate-700/80 transition-all group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Sales</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl sm:text-3xl font-bold text-white">${{ number_format($totalRevenue, 2) }}</div>
                <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                    <span class="text-emerald-400 font-semibold">Active</span> across all completed orders
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg backdrop-blur-sm hover:border-slate-700/80 transition-all group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Orders</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl sm:text-3xl font-bold text-white">{{ $totalOrders }}</div>
                <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                    <a href="{{ route('admin.orders.index') }}" class="text-indigo-400 hover:underline">View customer orders &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg backdrop-blur-sm hover:border-slate-700/80 transition-all group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                    {{ $isVendor ? 'Your Products' : 'Total Products' }}
                </span>
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 group-hover:scale-110 transition-transform">
                    <i data-lucide="package" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl sm:text-3xl font-bold text-white">{{ $totalProducts }}</div>
                <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                    @if($lowStockCount > 0)
                        <span class="text-rose-400 font-semibold">{{ $lowStockCount }} low in stock</span>
                    @else
                        <span class="text-emerald-400 font-semibold">Healthy inventory levels</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Banners & Promotions -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 shadow-lg backdrop-blur-sm hover:border-slate-700/80 transition-all group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active Banners</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                    <i data-lucide="image" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl sm:text-3xl font-bold text-white">{{ $totalBanners }}</div>
                <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                    <a href="{{ route('admin.banners.index') }}" class="text-amber-400 hover:underline">Manage promotions &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 backdrop-blur-sm">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4">Quick Management Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @if(auth()->user()->hasPermission('manage_products'))
            <a href="{{ route('admin.products.create') }}" 
               class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 hover:border-brand-500/50 transition-all group">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-white">Add Product</div>
                    <div class="text-[10px] text-slate-400">Upload new item</div>
                </div>
            </a>
            @endif

            @if(auth()->user()->hasPermission('manage_banners'))
            <a href="{{ route('admin.banners.create') }}" 
               class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 hover:border-brand-500/50 transition-all group">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-white">Upload Banner</div>
                    <div class="text-[10px] text-slate-400">Hero slider promo</div>
                </div>
            </a>
            @endif

            @if(auth()->user()->hasPermission('manage_categories'))
            <a href="{{ route('admin.categories.create') }}" 
               class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 hover:border-brand-500/50 transition-all group">
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center group-hover:bg-purple-500 group-hover:text-white transition-colors">
                    <i data-lucide="folder-plus" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-white">Add Category</div>
                    <div class="text-[10px] text-slate-400">Catalog organization</div>
                </div>
            </a>
            @endif

            @if(auth()->user()->hasPermission('manage_users'))
            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 hover:border-brand-500/50 transition-all group">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-white">User Permissions</div>
                    <div class="text-[10px] text-slate-400">Manage vendor access</div>
                </div>
            </a>
            @endif
        </div>
    </div>

    <!-- Two Column Layout: Recent Orders & Recent Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Recent Orders -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 shadow-xl backdrop-blur-sm flex flex-col">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Recent Customer Orders</h3>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-semibold">View All &rarr;</a>
            </div>

            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 uppercase tracking-wider">
                            <th class="pb-3 font-semibold">Order</th>
                            <th class="pb-3 font-semibold">Customer</th>
                            <th class="pb-3 font-semibold">Total</th>
                            <th class="pb-3 font-semibold text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 font-mono font-semibold text-white">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="text-brand-400 hover:underline">
                                    #{{ substr($order->orderNumber, 0, 10) }}
                                </a>
                            </td>
                            <td class="py-3 text-slate-300">{{ $order->customerName ?: 'Customer' }}</td>
                            <td class="py-3 font-bold text-white">${{ number_format($order->totalPrice, 2) }}</td>
                            <td class="py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    {{ $order->status === 'delivered' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 
                                      ($order->status === 'cancelled' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 
                                      'bg-amber-500/15 text-amber-400 border border-amber-500/30') }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-500 italic">No orders received yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Products -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 shadow-xl backdrop-blur-sm flex flex-col">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                        <i data-lucide="box" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Latest Catalog Additions</h3>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-semibold">All Products &rarr;</a>
            </div>

            <div class="space-y-3 flex-1">
                @forelse($recentProducts as $product)
                @php
                    $thumb = is_array($product->images) && count($product->images) > 0 ? $product->images[0] : null;
                @endphp
                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-950/50 border border-slate-800/80 hover:border-slate-700 transition-all">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-slate-800 overflow-hidden flex-shrink-0 border border-slate-700/60">
                            @if($thumb)
                                <img src="{{ $thumb }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-500">
                                    <i data-lucide="package" class="w-5 h-5"></i>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-white truncate">{{ $product->name }}</h4>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs font-semibold text-emerald-400">${{ number_format($product->price, 2) }}</span>
                                <span class="text-[10px] text-slate-400">• {{ $product->stock }} in stock</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('admin.products.edit', $product->id) }}" 
                           class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="py-6 text-center text-slate-500 italic">No products added yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Best Sellers Showcase -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 shadow-xl backdrop-blur-sm">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i data-lucide="flame" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Top Best Sellers</h3>
                    <p class="text-xs text-slate-400">Ranked by verified customer purchases (falls back to array serial if 0 sales)</p>
                </div>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-semibold">View Catalog &rarr;</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @forelse($bestSellers as $index => $product)
            @php
                $thumb = is_array($product->images) && count($product->images) > 0 ? $product->images[0] : null;
                $sales = (int)($product->sales_count ?? 0);
            @endphp
            <div class="relative bg-slate-950/60 border border-slate-800/80 rounded-2xl p-4 flex flex-col justify-between hover:border-amber-500/40 transition-all group">
                <!-- Rank badge -->
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $index === 0 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-slate-800 text-slate-300' }}">
                        #{{ $index + 1 }}
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md {{ $sales > 0 ? 'bg-emerald-500/15 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                        <i data-lucide="trending-up" class="w-3 h-3"></i>
                        {{ $sales }} Sold
                    </span>
                </div>

                <!-- Product Thumbnail -->
                <div class="aspect-square w-full rounded-xl bg-slate-900 overflow-hidden mb-3 border border-slate-800 flex items-center justify-center">
                    @if($thumb)
                        <img src="{{ $thumb }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <i data-lucide="package" class="w-8 h-8 text-slate-600"></i>
                    @endif
                </div>

                <!-- Product Details -->
                <div class="space-y-1">
                    <h4 class="text-xs font-bold text-white line-clamp-1 group-hover:text-amber-300 transition-colors" title="{{ $product->name }}">
                        {{ $product->name }}
                    </h4>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-emerald-400">${{ number_format($product->price, 2) }}</span>
                        <span class="text-[10px] text-slate-400">{{ $product->stock }} in stock</span>
                    </div>
                </div>

                <!-- Action button -->
                <a href="{{ route('admin.products.edit', $product->id) }}" 
                   class="mt-3 w-full py-1.5 px-3 text-[11px] font-semibold text-center rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors">
                    Manage Product
                </a>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-slate-500 italic">No products available in store.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
