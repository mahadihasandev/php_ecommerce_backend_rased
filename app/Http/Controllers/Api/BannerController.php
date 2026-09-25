<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = \Illuminate\Support\Facades\Cache::remember('api_banners', 60, function () {
            return Banner::all()->toArray();
        });

        return response()->json($banners)
            ->header('Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600');
    }
}
