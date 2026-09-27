<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Brand::all()->toArray());
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
