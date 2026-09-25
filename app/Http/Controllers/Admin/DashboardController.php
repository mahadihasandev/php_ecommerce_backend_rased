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
    public function index()
    {
        $user = Auth::user();
        $isVendor = $user->isVendor();
        $cacheKey = 'admin_dashboard_scalar_metrics_' . $user->id;

        // Cache scalar numbers for 120s to ensure fast page loads
        $metrics = Cache::remember($cacheKey, 120, function () use ($user, $isVendor) {
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

            return compact(
                'totalProducts',
                'lowStockCount',
                'totalOrders',
                'totalRevenue',
                'totalBanners',
                'totalCategories',
                'totalBrands',
                'totalVendors',
                'totalUsers'
            );
        });

        // Query latest 5 products and 6 orders directly
        $productQuery = Product::query();
        if ($isVendor) {
            $productQuery->where('user_id', $user->id);
        }

        $recentProducts = (clone $productQuery)->with(['brand', 'categories'])->latest()->take(5)->get();
        $recentOrders = Order::latest()->take(6)->get();

        // Cache best-seller product IDs (pure integer array) for 120s to skip expensive aggregation
        $bestSellerCacheKey = 'admin_bestseller_ids_' . ($isVendor ? $user->id : 'all');
        $bestSellerIds = Cache::remember($bestSellerCacheKey, 120, function () use ($isVendor, $user) {
            $query = Product::query()
                ->withSum(['orderItems as sales_count' => function ($q) {
                    $q->whereHas('order', function ($sub) {
                        $sub->where('status', '!=', 'cancelled');
                    });
                }], 'quantity');

            if ($isVendor) {
                $query->where('user_id', $user->id);
            }

            return $query
                ->orderByDesc('sales_count')
                ->orderBy('id', 'asc')
                ->take(5)
                ->pluck('id')
                ->toArray();
        });

        $bestSellers = !empty($bestSellerIds)
            ? Product::with(['brand', 'categories'])->whereIn('id', $bestSellerIds)->get()
            : collect();

        return view('admin.dashboard', array_merge($metrics, compact(
            'isVendor',
            'recentProducts',
            'recentOrders',
            'bestSellers'
        )));
    }
}
