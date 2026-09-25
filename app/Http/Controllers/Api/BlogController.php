<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::with(['author', 'blogcategories'])->latest('publishedAt');

        if ($request->has('quantity')) {
            $query->limit((int) $request->get('quantity'));
        }

        return response()->json($query->get());
    }

    public function latest()
    {
        $blogs = Blog::with(['author', 'blogcategories'])
            ->latest('publishedAt')
            ->limit(3)
            ->get();

        return response()->json($blogs);
    }

    public function show($slug)
    {
        $blog = Blog::with(['author', 'blogcategories'])
            ->where('slug', $slug)
            ->orWhere('_id', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        return response()->json($blog);
    }

    public function others(Request $request)
    {
        $excludeSlug = $request->get('exclude');
        $limit = (int) $request->get('limit', 3);

        $query = Blog::with(['author', 'blogcategories'])
            ->when($excludeSlug, function ($q) use ($excludeSlug) {
                $q->where('slug', '!=', $excludeSlug);
            })
            ->latest('publishedAt')
            ->limit($limit);

        return response()->json($query->get());
    }

    public function categories()
    {
        return response()->json(BlogCategory::all());
    }
}
