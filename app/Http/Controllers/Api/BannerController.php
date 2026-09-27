<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\PerformanceCache;

class BannerController extends Controller
{
    public function index()
    {
        $banners = PerformanceCache::remember('api_banners', function () {
            return Banner::all()->toArray();
        });

        return response()->json($banners)
            ->header('Cache-Control', 'public, max-age=15, s-maxage=15');
    }
}
