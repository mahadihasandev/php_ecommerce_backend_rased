<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $quantity = (int) $request->get('quantity', 0);
        $cacheKey = 'api_categories_' . $quantity;

        $categories = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($quantity) {
            $query = Category::query();
            if ($quantity > 0) {
                $query->limit($quantity);
            }
            return $query->get()->toArray();
        });

        return response()->json($categories)
            ->header('Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600');
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->orWhere('_id', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        return response()->json($category);
    }
}
