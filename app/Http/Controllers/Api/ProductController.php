<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\PerformanceCache;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List products with flexible filtering.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['variant', 'category', 'brand', 'minPrice', 'maxPrice', 'limit', 'per_page', 'page', 'paginate']);
        ksort($filters);
        $cacheKey = 'api_products_'.hash('sha256', $request->url().json_encode($filters));

        $products = PerformanceCache::remember($cacheKey, function () use ($request) {
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

            $perPage = max(1, min(100, (int) $request->get('limit', $request->get('per_page', 20))));
            $page = max(1, (int) $request->get('page', 1));

            // Support both standard paginator object and page-sliced array
            if ($request->boolean('paginate')) {
                return $query->latest()->paginate($perPage)->toArray();
            }

            // Return page-sliced array for direct consumption
            return $query->latest()->forPage($page, $perPage)->get()->toArray();
        });

        return response()->json($products)
            ->header('Cache-Control', 'public, max-age=15, s-maxage=15');
    }

    /**
     * Fetch a single product by slug or ID.
     */
    public function show($slug)
    {
        $product = PerformanceCache::remember('api_product_'.hash('sha256', (string) $slug), function () use ($slug) {
            $query = Product::with(['brand', 'categories']);

            if (is_numeric($slug)) {
                $query->where(function ($q) use ($slug) {
                    $q->where('id', (int) $slug)
                        ->orWhere('slug', $slug)
                        ->orWhere('_id', $slug);
                });
            } else {
                $query->where(function ($q) use ($slug) {
                    $q->where('slug', $slug)
                        ->orWhere('_id', $slug)
                        ->orWhereRaw('LOWER(slug) = ?', [strtolower($slug)]);
                });
            }

            return $query->firstOrFail()->toArray();
        });

        return response()->json($product);
    }

    /**
     * Fetch hot deals and discounted products.
     */
    public function hotDeals()
    {
        $products = PerformanceCache::remember('api_hot_deals', function () {
            return Product::with(['brand', 'categories'])
                ->where('status', 'hot')
                ->orWhere('discount', '>', 10)
                ->latest()
                ->limit(10)
                ->get()
                ->toArray();
        });

        return response()->json($products)
            ->header('Cache-Control', 'public, max-age=15, s-maxage=15');
    }

    /**
     * Fetch best selling products ranked by sales count with pagination support.
     * When no products have sales yet (or sales count is 0), returns products in natural array serial (id asc).
     */
    public function bestSellers(Request $request)
    {
        $limit = max(1, min(100, (int) $request->get('limit', $request->get('per_page', 10))));
        $page = max(1, (int) $request->get('page', 1));
        $cacheKey = 'api_best_sellers_'.hash('sha256', $request->url()).'_'.$limit.'_p_'.$page.($request->boolean('paginate') ? '_pag' : '');

        $products = PerformanceCache::remember($cacheKey, function () use ($limit, $page, $request) {
            $query = Product::with(['brand', 'categories'])
                ->rankedBySales();

            if ($request->boolean('paginate')) {
                $paginated = $query->paginate($limit);
                $paginated->getCollection()->transform(function ($product) {
                    $product->sales_count = (int) ($product->sales_count ?? 0);

                    return $product;
                });

                return $paginated->toArray();
            }

            $items = $query->forPage($page, $limit)->get();
            $items->transform(function ($product) {
                $product->sales_count = (int) ($product->sales_count ?? 0);

                return $product;
            });

            return $items->toArray();
        }, ['catalog', 'orders']);

        return response()->json($products)
            ->header('Cache-Control', 'public, max-age=15, s-maxage=15');
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

        $products = PerformanceCache::remember('api_search_'.hash('sha256', $keyword), function () use ($keyword) {
            $driver = \Illuminate\Support\Facades\DB::getDriverName();
            $like = $driver === 'pgsql' ? 'ilike' : 'like';
            $spaceVariant = str_replace(['-', '_'], ' ', $keyword);
            $dashVariant = str_replace(' ', '-', strtolower($keyword));

            // 1. Try exact / whole phrase match
            $results = Product::with(['brand', 'categories'])
                ->where(function ($q) use ($keyword, $spaceVariant, $dashVariant, $like) {
                    $q->where('name', $like, "%{$keyword}%")
                        ->orWhere('slug', $like, "%{$keyword}%")
                        ->orWhere('variant', $like, "%{$keyword}%")
                        ->orWhere('name', $like, "%{$spaceVariant}%")
                        ->orWhere('slug', $like, "%{$dashVariant}%");
                })
                ->latest()
                ->get();

            if ($results->isNotEmpty()) {
                return $results->toArray();
            }

            // 2. Tokenized fallback for compound queries (e.g., bose-quietcomfort-ultra-earbuds)
            $words = array_values(array_filter(preg_split('/[\s\-_]+/', $keyword), fn ($w) => strlen($w) >= 3));
            if (!empty($words)) {
                $tokenQuery = Product::with(['brand', 'categories'])
                    ->where(function ($q) use ($words, $like) {
                        foreach ($words as $w) {
                            $q->orWhere('name', $like, "%{$w}%")
                              ->orWhere('slug', $like, "%{$w}%");
                        }
                    })
                    ->latest()
                    ->limit(20)
                    ->get();

                if ($tokenQuery->isNotEmpty()) {
                    return $tokenQuery->toArray();
                }
            }

            return [];
        });

        return response()->json($products);
    }
}
