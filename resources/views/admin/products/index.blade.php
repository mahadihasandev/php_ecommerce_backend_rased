@extends('admin.layouts.app')

@section('title', 'Products Catalog')
@section('page_title', 'Products Inventory & Catalog')
@section('page_subtitle', 'Manage item specifications, multi-image galleries, pricing, and stock')

@section('content')
<div class="space-y-6">

    <!-- Filters & Action Header -->
    <div class="bg-slate-900/60 p-6 rounded-3xl border border-slate-800/80 backdrop-blur-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white">Catalog Products</h2>
                <p class="text-xs text-slate-400 mt-0.5">Search, filter by category, and update your item inventory.</p>
            </div>
            <a href="{{ route('admin.products.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-500/25 transition-all">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Add New Product</span>
            </a>
        </div>

        <!-- Search and Filter Form -->
        <form action="{{ route('admin.products.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2 border-t border-slate-800/80">
            <!-- Search -->
            <div class="sm:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by product name or slug..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <!-- Category Filter -->
            <div>
                <select name="category_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Status Filter -->
            <div>
                <select name="stock_status" onchange="this.form.submit()"
                        class="w-full px-3 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-indigo-500">
                    <option value="">All Stock Levels</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>⚠️ Low Stock (&le; 5)</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>❌ Out of Stock (0)</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl overflow-hidden shadow-xl backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 border-b border-slate-800 uppercase tracking-wider font-semibold">
                        <th class="py-4 px-6">Product</th>
                        <th class="py-4 px-4">Categories</th>
                        <th class="py-4 px-4">Brand</th>
                        <th class="py-4 px-4">Price</th>
                        <th class="py-4 px-4">Stock</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4">Vendor</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($products as $product)
                    @php
                        $thumb = is_array($product->images) && count($product->images) > 0 ? $product->images[0] : null;
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <!-- Product Title & Image -->
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
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
                                    <div class="font-bold text-white truncate max-w-xs">{{ $product->name }}</div>
                                    <div class="text-[11px] font-mono text-slate-400 truncate max-w-xs">{{ $product->slug_text }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Categories -->
                        <td class="py-4 px-4">
                            <div class="flex flex-wrap gap-1 max-w-[150px]">
                                @forelse($product->categories as $cat)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-800 text-slate-300 border border-slate-700/50">
                                        {{ $cat->title }}
                                    </span>
                                @empty
                                    <span class="text-slate-500 italic">None</span>
                                @endforelse
                            </div>
                        </td>

                        <!-- Brand -->
                        <td class="py-4 px-4 text-slate-300">
                            {{ $product->brand?->title ?: '—' }}
                        </td>

                        <!-- Price -->
                        <td class="py-4 px-4">
                            <div class="font-bold text-white">${{ number_format($product->price, 2) }}</div>
                            @if($product->discount)
                                <div class="text-[11px] text-emerald-400 font-semibold">${{ number_format($product->discount, 2) }}</div>
                            @endif
                        </td>

                        <!-- Stock -->
                        <td class="py-4 px-4">
                            @if($product->stock <= 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                    Out of stock
                                </span>
                            @elseif($product->stock <= 5)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                    {{ $product->stock }} Left
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                    {{ $product->stock }} In Stock
                                </span>
                            @endif
                        </td>

                        <!-- Status & Badges -->
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-1.5">
                                @if($product->status)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        {{ $product->status === 'hot' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 
                                          ($product->status === 'new' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 
                                          'bg-amber-500/20 text-amber-300 border border-amber-500/30') }}">
                                        {{ $product->status }}
                                    </span>
                                @endif
                                @if($product->isFeatured)
                                    <span title="Featured Product" class="text-amber-400">
                                        <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Vendor -->
                        <td class="py-4 px-4">
                            <span class="text-slate-300 font-medium">
                                {{ $product->user?->store_name ?: ($product->user?->name ?: 'Store Admin') }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.products.edit', $product->id) }}" 
                                   class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors" title="Edit">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" 
                                      onsubmit="return confirm('Delete this product permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition-colors" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-500 italic">
                            No products match your search or filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="p-4 border-t border-slate-800/80">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
