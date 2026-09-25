@extends('admin.layouts.app')

@section('title', 'Customer Orders')
@section('page_title', 'Customer Orders & Fulfillment')
@section('page_subtitle', 'Review customer purchases, addresses, line items, and fulfillment stages')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Search Header -->
    <div class="bg-slate-900/60 p-6 rounded-3xl border border-slate-800/80 backdrop-blur-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white">All Orders</h2>
                <p class="text-xs text-slate-400 mt-0.5">Filter by status or search for specific customer orders.</p>
            </div>
            
            <!-- Search Input -->
            <form action="{{ route('admin.orders.index') }}" method="GET" class="w-full sm:w-72">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, name, or email..."
                           class="w-full pl-10 pr-4 py-2 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                </div>
            </form>
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pt-2 border-t border-slate-800/80 scrollbar-none">
            @foreach(['all' => 'All Orders', 'pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $key => $label)
            <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => $key === 'all' ? null : $key])) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5
                   {{ (request('status') === $key || (empty(request('status')) && $key === 'all')) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'bg-slate-950/40 text-slate-400 hover:text-white border border-slate-800' }}">
                <span>{{ $label }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/15">
                    {{ $statusCounts[$key] ?? 0 }}
                </span>
            </a>
            @endforeach
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl overflow-hidden shadow-xl backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 border-b border-slate-800 uppercase tracking-wider font-semibold">
                        <th class="py-4 px-6">Order Number</th>
                        <th class="py-4 px-4">Customer</th>
                        <th class="py-4 px-4">Date</th>
                        <th class="py-4 px-4">Items</th>
                        <th class="py-4 px-4">Total</th>
                        <th class="py-4 px-4">Fulfillment</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="py-4 px-6 font-mono font-bold text-white">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-brand-400 hover:underline">
                                #{{ $order->orderNumber }}
                            </a>
                        </td>
                        <td class="py-4 px-4">
                            <div class="font-bold text-white">{{ $order->customerName ?: 'Customer' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $order->email }}</div>
                        </td>
                        <td class="py-4 px-4 text-slate-400">
                            {{ $order->created_at->format('M d, Y') }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700/60">
                                {{ $order->orderItems->count() }} items
                            </span>
                        </td>
                        <td class="py-4 px-4 font-bold text-white">
                            ${{ number_format($order->totalPrice, 2) }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $order->status === 'delivered' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 
                                  ($order->status === 'cancelled' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 
                                  ($order->status === 'shipped' ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' :
                                  'bg-amber-500/15 text-amber-400 border border-amber-500/30')) }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="{{ route('admin.orders.show', $order->id) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-medium text-xs transition-colors border border-slate-700/60">
                                <span>Inspect</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500 italic">No orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-800/80">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
