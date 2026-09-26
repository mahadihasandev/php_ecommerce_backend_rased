<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = \Illuminate\Support\Facades\Cache::remember('api_brands', 60, function () {
            return Brand::all()->toArray();
        });

        return response()->json($brands)
            ->header('Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600');
    }

    public function show($slug)
    {
        $query = Brand::query();

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

        $brand = $query->firstOrFail();

        return response()->json($brand);
    }
}
