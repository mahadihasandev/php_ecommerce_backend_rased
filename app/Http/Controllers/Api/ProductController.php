<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List products with flexible filtering.
     */
    public function index(Request $request)
    {
        $cacheKey = 'api_products_' . md5(json_encode($request->all()));

        $products = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($request) {
            $query = Product::with(['brand', 'categories']);

            if ($request->filled('variant')) {
                $variant = strtolower($request->get('variant'));
                $query->whereRaw('LOWER(variant) = ?', [$variant]);
            }

            if ($request->filled('category')) {
                $cat = $request->get('category');
                $query->whereHas('categories', function ($q) use ($cat) {
                    $q->where('slug', $cat)
                        ->orWhere('_id', $cat)
                        ->orWhere('title', $cat);
                });
            }

            if ($request->filled('brand')) {
                $brand = $request->get('brand');
                $query->whereHas('brand', function ($q) use ($brand) {
                    $q->where('slug', $brand)
                        ->orWhere('_id', $brand)
                        ->orWhere('title', $brand)
                        ->orWhere('brandName', $brand);
                });
            }

            if ($request->filled('minPrice')) {
                $query->where('price', '>=', (float) $request->get('minPrice'));
            }

            if ($request->filled('maxPrice')) {
                $query->where('price', '<=', (float) $request->get('maxPrice'));
            }

            if ($request->has('limit')) {
                $query->limit((int) $request->get('limit'));
            }

            return $query->latest()->get()->toArray();
        });

        return response()->json($products)
            ->header('Cache-Control', 'public, max-age=60, s-maxage=120, stale-while-revalidate=300');
    }

    /**
     * Fetch a single product by slug or ID.
     */
    public function show($slug)
    {
        $product = Product::with(['brand', 'categories'])
            ->where('slug', $slug)
            ->orWhere('_id', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        return response()->json($product);
    }

    /**
     * Fetch hot deals and discounted products.
     */
    public function hotDeals()
    {
        $products = \Illuminate\Support\Facades\Cache::remember('api_hot_deals', 60, function () {
            return Product::with(['brand', 'categories'])
                ->where('status', 'hot')
                ->orWhere('discount', '>', 10)
                ->latest()
                ->limit(10)
                ->get()
                ->toArray();
        });

        return response()->json($products)
            ->header('Cache-Control', 'public, max-age=15, stale-while-revalidate=60');
    }

    /**
     * Fetch best selling products ranked by sales count.
     * When no products have sales yet (or sales count is 0), returns products in natural array serial (id asc).
     */
    public function bestSellers(Request $request)
    {
        $limit = max(1, min(50, (int) $request->get('limit', 10)));
        $cacheKey = 'api_best_sellers_' . $limit;

        $products = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($limit) {
            $query = Product::with(['brand', 'categories'])
                ->withSum(['orderItems as sales_count' => function ($q) {
                    $q->whereHas('order', function ($sub) {
                        $sub->where('status', '!=', 'cancelled');
                    });
                }], 'quantity')
                ->orderByDesc('sales_count')
                ->orderBy('id', 'asc')
                ->limit($limit)
                ->get();

            $query->transform(function ($product) {
                $product->sales_count = (int) ($product->sales_count ?? 0);
                return $product;
            });

            return $query->toArray();
        });

        return response()->json($products)
            ->header('Cache-Control', 'public, max-age=60, s-maxage=120, stale-while-revalidate=300');
    }

    /**
     * Search products by keyword.
     */
    public function search(Request $request)
    {
        $keyword = trim((string) $request->get('q', ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        $products = Product::with(['brand', 'categories'])
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('variant', 'like', "%{$keyword}%");
            })
            ->latest()
            ->get();

        return response()->json($products);
    }
}
