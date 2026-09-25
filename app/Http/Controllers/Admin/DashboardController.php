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
        $cacheKey = 'admin_dashboard_metrics_' . $user->id;

        // Cache heavy metrics for 30s to make navigation lightning fast on remote database
        $metrics = Cache::remember($cacheKey, 30, function () use ($user, $isVendor) {
            $productQuery = Product::query();
            if ($isVendor) {
                $productQuery->where('user_id', $user->id);
            }

            $totalProducts = (clone $productQuery)->count();
            $lowStockCount = (clone $productQuery)->where('stock', '<=', 5)->count();
            $recentProducts = (clone $productQuery)->with(['brand', 'categories'])->latest()->take(5)->get();

            // Orders metrics
            $totalOrders = Order::count();
            $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('totalPrice');
            $recentOrders = Order::latest()->take(6)->get();

            // Other metrics
            $totalBanners = Banner::count();
            $totalCategories = Category::count();
            $totalBrands = Brand::count();
            $totalVendors = User::where('role', 'vendor')->count();
            $totalUsers = User::count();

            // Best selling products ranked by sales count, fallback to array serial (id asc)
            $bestSellersQuery = Product::query()
                ->with(['brand', 'categories'])
                ->withSum(['orderItems as sales_count' => function ($query) {
                    $query->whereHas('order', function ($q) {
                        $q->where('status', '!=', 'cancelled');
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
                'recentProducts',
                'bestSellers',
                'totalOrders',
                'totalRevenue',
                'recentOrders',
                'totalBanners',
                'totalCategories',
                'totalBrands',
                'totalVendors',
                'totalUsers'
            );
        });

        return view('admin.dashboard', array_merge($metrics, compact('isVendor')));
    }
}
