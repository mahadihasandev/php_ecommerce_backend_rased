<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\PerformanceCache;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $data = PerformanceCache::remember('api_blogs_'.hash('sha256', json_encode($request->only('quantity'))), function () use ($request) {
            $query = Blog::with(['author', 'blogcategories'])->latest('publishedAt');

            if ($request->has('quantity')) {
                $query->limit((int) $request->get('quantity'));
            }

            return $query->get()->toArray();
        });

        return response()->json($data);
    }

    public function latest()
    {
        $data = PerformanceCache::remember('api_blogs_latest', function () {
            $blogs = Blog::with(['author', 'blogcategories'])
                ->latest('publishedAt')
                ->limit(3)
                ->get();

            return $blogs->toArray();
        });

        return response()->json($data);
    }

    public function show($slug)
    {
        $data = PerformanceCache::remember('api_blog_'.hash('sha256', (string) $slug), function () use ($slug) {
            $query = Blog::with(['author', 'blogcategories']);

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

            $blog = $query->firstOrFail();

            return $blog->toArray();
        });

        return response()->json($data);
    }

    public function others(Request $request)
    {
        $data = PerformanceCache::remember('api_blogs_others_'.hash('sha256', json_encode($request->only(['exclude', 'limit']))), function () use ($request) {
            $excludeSlug = $request->get('exclude');
            $limit = (int) $request->get('limit', 3);

            $query = Blog::with(['author', 'blogcategories'])
                ->when($excludeSlug, function ($q) use ($excludeSlug) {
                    $q->where('slug', '!=', $excludeSlug);
                })
                ->latest('publishedAt')
                ->limit($limit);

            return $query->get()->toArray();
        });

        return response()->json($data);
    }

    public function categories()
    {
        $data = PerformanceCache::remember('api_blog_categories', function () {
            return BlogCategory::all()->toArray();
        });

        return response()->json($data);
    }
}
