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

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isVendor = $user->isVendor();

        // Product queries scoped by vendor if needed
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

        return view('admin.dashboard', compact(
            'totalProducts',
            'lowStockCount',
            'recentProducts',
            'totalOrders',
            'totalRevenue',
            'recentOrders',
            'totalBanners',
            'totalCategories',
            'totalBrands',
            'totalVendors',
            'totalUsers',
            'isVendor'
        ));
    }
}
