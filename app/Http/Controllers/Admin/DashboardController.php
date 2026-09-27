<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PerformanceCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Render the admin dashboard with high-performance caching.
     */
    public function index()
    {
        $user = Auth::user();
        $isVendor = $user->isVendor();
        $cacheKey = 'admin_dashboard_'.$user->role.'_'.$user->id;

        // Keep the authenticated user and role in the key to preserve vendor scoping.
        $data = PerformanceCache::remember($cacheKey, function () use ($user, $isVendor) {
            $productQuery = Product::query();
            if ($isVendor) {
                $productQuery->where('user_id', $user->id);
            }

            // Each aggregate returns one row. Combining them in one statement avoids
            // six separate network round trips to the remote production database.
            $productMetrics = (clone $productQuery)
                ->selectRaw('COUNT(*) as total_products, SUM(CASE WHEN stock <= 5 THEN 1 ELSE 0 END) as low_stock');
            $totalPriceColumn = Order::query()->getQuery()->getGrammar()->wrap('totalPrice');
            $orderMetrics = Order::query()->selectRaw('COUNT(*) as total_orders')
                ->selectRaw('SUM(CASE WHEN status != ? THEN '.$totalPriceColumn.' ELSE 0 END) as revenue', ['cancelled']);
            $userMetrics = User::query()
                ->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN role = ? THEN 1 ELSE 0 END) as vendors', ['vendor']);

            $metrics = DB::query()->fromSub($productMetrics, 'product_metrics')
                ->crossJoinSub($orderMetrics, 'order_metrics')
                ->crossJoinSub($userMetrics, 'user_metrics')
                ->select(['product_metrics.*', 'order_metrics.*', 'user_metrics.*'])
                ->selectSub(Banner::query()->selectRaw('COUNT(*)'), 'total_banners')
                ->selectSub(Category::query()->selectRaw('COUNT(*)'), 'total_categories')
                ->selectSub(Brand::query()->selectRaw('COUNT(*)'), 'total_brands')
                ->first();

            $totalProducts = (int) $metrics->total_products;
            $lowStockCount = (int) $metrics->low_stock;
            $totalOrders = (int) $metrics->total_orders;
            $totalRevenue = (float) $metrics->revenue;
            $totalBanners = (int) $metrics->total_banners;
            $totalCategories = (int) $metrics->total_categories;
            $totalBrands = (int) $metrics->total_brands;
            $totalVendors = (int) $metrics->vendors;
            $totalUsers = (int) $metrics->total_users;

            // These cards only render product scalars; no brand/category queries are needed.
            $recentProducts = (clone $productQuery)->select(['id', 'name', 'price', 'stock', 'images'])->latest()->take(5)->get();

            // Recent customer orders
            $recentOrders = Order::select(['id', 'orderNumber', 'customerName', 'totalPrice', 'status'])->latest()->take(6)->get();

            // Top best sellers with sales count
            $bestSellersQuery = Product::query()
                ->select(['products.id', 'products.name', 'products.price', 'products.stock', 'products.images'])
                ->rankedBySales();

            if ($isVendor) {
                $bestSellersQuery->where('user_id', $user->id);
            }

            $bestSellers = $bestSellersQuery
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
            ! is_array($data) ||
            (isset($data['recentOrders']) && $data['recentOrders'] instanceof \__PHP_Incomplete_Class) ||
            (isset($data['recentProducts']) && $data['recentProducts'] instanceof \__PHP_Incomplete_Class) ||
            (isset($data['bestSellers']) && $data['bestSellers'] instanceof \__PHP_Incomplete_Class)
        ) {
            PerformanceCache::invalidate();

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
        PerformanceCache::invalidate();

        return back()->with('success', 'Dashboard & API cache purged successfully! Fresh data loaded.');
    }
}
