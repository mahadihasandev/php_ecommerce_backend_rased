<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\PerformanceCache;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $quantity = (int) $request->get('quantity', 0);
        $cacheKey = 'api_categories_'.$quantity;

        $categories = PerformanceCache::remember($cacheKey, function () use ($quantity) {
            $query = Category::query();
            if ($quantity > 0) {
                $query->limit($quantity);
            }

            return $query->get()->toArray();
        });

        return response()->json($categories)
            ->header('Cache-Control', 'public, max-age=15, s-maxage=15');
    }

    public function show($slug)
    {
        $category = PerformanceCache::remember('api_category_'.hash('sha256', (string) $slug), function () use ($slug) {
            $query = Category::query();

            if (is_numeric($slug)) {
                $query->where(function ($q) use ($slug) {
                    $q->where('id', (int) $slug)
                        ->orWhere('slug', $slug)
                        ->orWhere('_id', $slug);
                });
            } else {
                $query->where(function ($q) use ($slug) {
                    $q->where('slug', $slug)
                        ->orWhere('_id', $slug);
                });
            }

            return $query->firstOrFail()->toArray();
        });

        return response()->json($category);
    }
}
