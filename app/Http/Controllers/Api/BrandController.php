<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\PerformanceCache;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = PerformanceCache::remember('api_brands', function () {
            return Brand::all()->toArray();
        });

        return response()->json($brands)
            ->header('Cache-Control', 'public, max-age=15, s-maxage=15');
    }

    public function show($slug)
    {
        $brand = PerformanceCache::remember('api_brand_'.hash('sha256', (string) $slug), function () use ($slug) {
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

            return $query->firstOrFail()->toArray();
        });

        return response()->json($brand);
    }
}
