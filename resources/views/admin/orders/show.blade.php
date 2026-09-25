@extends('admin.layouts.app')

@section('title', 'Order #' . $order->orderNumber)
@section('page_title', 'Order Fulfillment Details')
@section('page_subtitle', 'Review order items, customer shipping address, and update status')

@section('content')
<div class="space-y-6">

    <!-- Top Summary Banner -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-xl backdrop-blur-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl sm:text-2xl font-bold font-mono text-white">#{{ $order->orderNumber }}</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                    {{ $order->status === 'delivered' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 
                      ($order->status === 'cancelled' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 
                      ($order->status === 'shipped' ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' :
                      'bg-amber-500/15 text-amber-400 border border-amber-500/30')) }}">
                    {{ $order->status }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>

        <!-- Quick Status Update Form -->
        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="flex items-center gap-3 bg-slate-950/60 p-2.5 rounded-2xl border border-slate-800">
            @csrf
            @method('PATCH')
            <label class="text-xs font-semibold text-slate-400 pl-2">Update Stage:</label>
            <select name="status" class="px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-brand-500">
                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="px-4 py-1.5 rounded-xl text-xs font-semibold text-white bg-brand-600 hover:bg-brand-500 transition-colors shadow">
                Save
            </button>
        </form>
    </div>

    <!-- Main Grid: Items & Customer Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Order Items Column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 shadow-xl backdrop-blur-sm">
                <h3 class="text-base font-bold text-white mb-4">Purchased Items</h3>

                <div class="divide-y divide-slate-800/80">
                    @forelse($order->orderItems as $item)
                    @php
                        $product = $item->product;
                        $img = $product && is_array($product->images) && count($product->images) > 0 ? $product->images[0] : null;
                    @endphp
                    <div class="py-4 flex items-center justify-between gap-4 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-14 h-14 rounded-xl bg-slate-800 overflow-hidden flex-shrink-0 border border-slate-700/60">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-500">
                                        <i data-lucide="package" class="w-6 h-6"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-white truncate">{{ $product?->name ?: 'Item' }}</h4>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    Qty: <span class="font-semibold text-slate-200">{{ $item->quantity }}</span> &times; ${{ number_format($item->price, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <div class="text-sm font-bold text-white">
                                ${{ number_format($item->price * $item->quantity, 2) }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-slate-500 italic">No line items recorded.</div>
                    @endforelse
                </div>

                <!-- Financial Totals Breakdown -->
                <div class="mt-6 pt-5 border-t border-slate-800/80 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Items Subtotal</span>
                        <span>${{ number_format($order->totalPrice, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Shipping & Handling</span>
                        <span class="text-emerald-400 font-semibold">Free Delivery</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-white pt-2 border-t border-slate-800/60">
                        <span>Grand Total</span>
                        <span class="text-brand-400">${{ number_format($order->totalPrice, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Customer Info -->
        <div class="space-y-6">
            
            <!-- Customer Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 shadow-xl backdrop-blur-sm space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Customer Details</h3>
                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-500 block">Name</span>
                        <span class="text-white font-semibold">{{ $order->customerName ?: 'Guest Customer' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Email Address</span>
                        <span class="text-white font-semibold">{{ $order->email }}</span>
                    </div>
                    @if($order->address?->phone)
                    <div>
                        <span class="text-slate-500 block">Phone</span>
                        <span class="text-white font-semibold">{{ $order->address->phone }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Shipping Address Card -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 shadow-xl backdrop-blur-sm space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Shipping Destination</h3>
                @if($order->address)
                <div class="text-xs text-slate-300 space-y-1">
                    <p class="font-bold text-white">{{ $order->address->name }}</p>
                    <p>{{ $order->address->address }}</p>
                    <p>{{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->postalCode }}</p>
                </div>
                @else
                <p class="text-xs text-slate-500 italic">No specific address linked for this order.</p>
                @endif
            </div>

            <a href="{{ route('admin.orders.index') }}" class="block text-center py-3 rounded-2xl bg-slate-800/60 hover:bg-slate-800 text-xs font-semibold text-slate-300 hover:text-white transition-colors border border-slate-700/60">
                &larr; Return to Orders List
            </a>
        </div>
    </div>
</div>
@endsection
