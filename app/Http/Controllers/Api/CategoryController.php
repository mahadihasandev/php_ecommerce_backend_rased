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

        $query = Category::query();
        if ($quantity > 0) {
            $query->limit($quantity);
        }

        return response()->json($query->get()->toArray());
    }

    public function show($slug)
    {
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

        $category = $query->firstOrFail();

        return response()->json($category);
    }
}
