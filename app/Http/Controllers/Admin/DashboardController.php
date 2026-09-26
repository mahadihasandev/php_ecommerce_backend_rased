<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Render the admin dashboard with high-performance caching.
     */
    public function index()
    {
        $user = Auth::user();
        $isVendor = $user->isVendor();
        $cacheKey = 'admin_dashboard_full_view_' . $user->id;

        // Cache the aggregated dashboard dataset for 60s to ensure sub-5ms page loads
        $data = Cache::remember($cacheKey, 60, function () use ($user, $isVendor) {
            $productQuery = Product::query();
            if ($isVendor) {
                $productQuery->where('user_id', $user->id);
            }

            $totalProducts = (clone $productQuery)->count();
            $lowStockCount = (clone $productQuery)->where('stock', '<=', 5)->count();

            // Orders metrics
            $totalOrders = Order::count();
            $totalRevenue = (float) (Order::where('status', '!=', 'cancelled')->sum('totalPrice') ?: 0);

            // Other metrics
            $totalBanners = Banner::count();
            $totalCategories = Category::count();
            $totalBrands = Brand::count();
            $totalVendors = User::where('role', 'vendor')->count();
            $totalUsers = User::count();

            // Recent products with eager-loaded relations
            $recentProducts = (clone $productQuery)->with(['brand', 'categories'])->latest()->take(5)->get();

            // Recent customer orders
            $recentOrders = Order::latest()->take(6)->get();

            // Top best sellers with sales count
            $bestSellersQuery = Product::query()
                ->with(['brand', 'categories'])
                ->withSum(['orderItems as sales_count' => function ($q) {
                    $q->whereHas('order', function ($sub) {
                        $sub->where('status', '!=', 'cancelled');
                    });
                }], 'quantity');

            if ($isVendor) {
                $bestSellersQuery->where('user_id', $user->id);
            }

            $bestSellers = $bestSellersQuery
                ->orderByDesc('sales_count')
                ->orderBy('id', 'asc')
                ->take(5)
                ->get();

            return compact(
                'totalProducts',
                'lowStockCount',
                'totalOrders',
                'totalRevenue',
                'totalBanners',
                'totalCategories',
                'totalBrands',
                'totalVendors',
                'totalUsers',
                'recentProducts',
                'recentOrders',
                'bestSellers'
            );
        });

        // Self-healing guard: if cached data was corrupted or contains incomplete classes, purge and reload
        if (
            !is_array($data) ||
            (isset($data['recentOrders']) && $data['recentOrders'] instanceof \__PHP_Incomplete_Class) ||
            (isset($data['recentProducts']) && $data['recentProducts'] instanceof \__PHP_Incomplete_Class) ||
            (isset($data['bestSellers']) && $data['bestSellers'] instanceof \__PHP_Incomplete_Class)
        ) {
            Cache::forget($cacheKey);
            return redirect()->route('admin.dashboard');
        }

        $cacheStore = config('cache.default', 'redis');

        return view('admin.dashboard', array_merge($data, compact('isVendor', 'cacheStore')));
    }

    /**
     * Instantly flush all admin dashboard caches.
     */
    public function purgeCache()
    {
        $user = Auth::user();
        if ($user) {
            Cache::forget('admin_dashboard_full_view_' . $user->id);
            Cache::forget('admin_dashboard_scalar_metrics_' . $user->id);
            Cache::forget('admin_bestseller_ids_' . ($user->isVendor() ? $user->id : 'all'));
        }

        // Also flush API cached endpoints for instant sync
        Cache::forget('api_hot_deals');
        Cache::forget('api_categories_0');
        Cache::forget('api_brands');

        return back()->with('success', 'Dashboard & API cache purged successfully! Fresh data loaded.');
    }
}
