<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $query = Order::with(['orderItems.product', 'address', 'user'])->latest();

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

        $orders = $query->paginate(15)->withQueryString();
        
        $statusCounts = \Illuminate\Support\Facades\Cache::remember('admin_order_status_counts', 60, function () {
            return [
                'all' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'shipped' => Order::where('status', 'shipped')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
            ];
        });

        return view('admin.orders.index', compact('orders', 'statusCounts', 'status'));
    }

    /**
     * Display the specified order details.
     */
    public function show($id)
    {
        $order = Order::with(['orderItems.product', 'address', 'user'])->findOrFail($id);
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

        return back()->with('success', "Order #{$order->orderNumber} status updated to " . ucfirst($validated['status']) . ".");
    }
}
