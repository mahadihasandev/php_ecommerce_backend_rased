<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * List orders for a specific user.
     */
    public function index(Request $request)
    {
        $userId = $request->get('user_id') ?? optional($request->user())->id;

        $orders = Order::with('products')
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->latest('orderDate')
            ->get();

        return response()->json($orders);
    }

    /**
     * Show single order details by orderNumber or ID.
     */
    public function show($identifier)
    {
        $order = Order::with('products')
            ->where('orderNumber', $identifier)
            ->orWhere('_id', $identifier)
            ->orWhere('id', $identifier)
            ->firstOrFail();

        return response()->json($order);
    }

    /**
     * Store order (from frontend checkout or Stripe webhook).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'orderNumber' => 'required|string',
            'customerName' => 'required|string',
            'email' => 'required|email',
            'totalPrice' => 'required|numeric',
            'user_id' => 'nullable|string',
            'amountDiscount' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'status' => 'nullable|string',
            'stripeCheckoutSessionId' => 'nullable|string',
            'stripeCustomerId' => 'nullable|string',
            'stripePaymentIntentId' => 'nullable|string',
            'invoice' => 'nullable|array',
            'address' => 'nullable|array',
            'products' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $order = Order::create([
                '_id' => 'ord-' . uniqid(),
                'orderNumber' => $validated['orderNumber'],
                'user_id' => $validated['user_id'] ?? optional($request->user())->id,
                'customerName' => $validated['customerName'],
                'email' => $validated['email'],
                'totalPrice' => $validated['totalPrice'],
                'amountDiscount' => $validated['amountDiscount'] ?? 0,
                'currency' => $validated['currency'] ?? 'BDT',
                'status' => $validated['status'] ?? 'paid',
                'orderDate' => now(),
                'stripeCheckoutSessionId' => $validated['stripeCheckoutSessionId'] ?? null,
                'stripeCustomerId' => $validated['stripeCustomerId'] ?? null,
                'stripePaymentIntentId' => $validated['stripePaymentIntentId'] ?? null,
                'invoice' => $validated['invoice'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            if (! empty($validated['products'])) {
                foreach ($validated['products'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['productId'] ?? $item['product_id'] ?? null,
                        'quantity' => $item['quantity'] ?? 1,
                        'price' => $item['price'] ?? 0,
                    ]);
                }
            }

            return response()->json($order->load('products'), 201);
        });
    }
}
