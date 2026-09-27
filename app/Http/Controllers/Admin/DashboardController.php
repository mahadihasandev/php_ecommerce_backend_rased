<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\StoreCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $cacheKey = StoreCache::key('dashboard:'.$user->id.':'.$user->role);

        // Cache data only; render session messages, CSRF tokens and permissions per request.
        $data = Cache::remember($cacheKey, StoreCache::TTL, function () use ($user, $isVendor) {
            $productQuery = Product::query();
            if ($isVendor) {
                $productQuery->where('user_id', $user->id);
            }

            // One database round trip for all counters, especially useful with a remote DB.
            $metrics = DB::query()
                ->selectSub((clone $productQuery)->selectRaw('COUNT(*)'), 'totalProducts')
                ->selectSub((clone $productQuery)->where('stock', '<=', 5)->selectRaw('COUNT(*)'), 'lowStockCount')
                ->selectSub(Order::selectRaw('COUNT(*)'), 'totalOrders')
                ->selectSub(Order::where('status', '!=', 'cancelled')->selectRaw('COALESCE(SUM('.DB::connection()->getQueryGrammar()->wrap('totalPrice').'), 0)'), 'totalRevenue')
                ->selectSub(Banner::selectRaw('COUNT(*)'), 'totalBanners')
                ->selectSub(Category::selectRaw('COUNT(*)'), 'totalCategories')
                ->selectSub(Brand::selectRaw('COUNT(*)'), 'totalBrands')
                ->selectSub(User::where('role', 'vendor')->selectRaw('COUNT(*)'), 'totalVendors')
                ->selectSub(User::selectRaw('COUNT(*)'), 'totalUsers')
                ->first();

            // Dashboard cards do not display brand/category relations or full descriptions.
            $recentProducts = (clone $productQuery)->select(['id', 'name', 'price', 'stock', 'images'])->latest()->take(5)->get();

            // Recent customer orders
            $recentOrders = Order::select(['id', 'orderNumber', 'customerName', 'totalPrice', 'status'])->latest()->take(6)->get();

            // Top best sellers with sales count
            $bestSellersQuery = Product::query()
                ->select(['id', 'name', 'price', 'stock', 'images'])
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

            return array_merge((array) $metrics, compact('recentProducts', 'recentOrders', 'bestSellers'));
        });

        // Self-healing guard: if cached data was corrupted or contains incomplete classes, purge and reload
        if (
            ! is_array($data) ||
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
        StoreCache::invalidate();

        return back()->with('success', 'Dashboard & API cache purged successfully! Fresh data loaded.');
    }
}
