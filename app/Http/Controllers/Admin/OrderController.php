<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PerformanceCache;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $query = Order::withCount('products')->latest();

        if ($status && in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('orderNumber', 'like', "%{$search}%")
                    ->orWhere('customerName', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $orders = PerformanceCache::remember(PerformanceCache::requestKey('admin_orders_', $request),
            fn () => $query->paginate(15)->withQueryString(), ['orders']);

        $statusCounts = PerformanceCache::remember('admin_order_status_counts', function () {
            $counts = Order::query()->select('status')->selectRaw('COUNT(*) as aggregate')
                ->groupBy('status')->pluck('aggregate', 'status')->map(fn ($count) => (int) $count);

            return ['all' => $counts->sum()] + array_replace(
                array_fill_keys(['pending', 'processing', 'shipped', 'delivered', 'cancelled'], 0),
                $counts->all()
            );
        }, ['orders']);

        return view('admin.orders.index', compact('orders', 'statusCounts', 'status'));
    }

    /**
     * Display the specified order details.
     */
    public function show($id)
    {
        $order = Order::with('products.product')->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the order fulfillment status.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', "Order #{$order->orderNumber} status updated to ".ucfirst($validated['status']).'.');
    }
}
