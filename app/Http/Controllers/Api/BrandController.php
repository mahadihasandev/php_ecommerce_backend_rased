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
        $brand = Brand::where('slug', $slug)
            ->orWhere('_id', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        return response()->json($brand);
    }
}
