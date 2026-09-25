<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Category;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    public function index()
    {
        $categories = Category::withCount('products')->latest()->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        $slug = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['title']);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $this->imageUploadService->upload($request->file('image'), 'categories');
        }

        $category = Category::create([
            'title' => $validated['title'],
            'name' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image' => $imageUrl,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->title}' created successfully!");
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,' . $id],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
        ]);

        $slug = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['title']);

        $imageUrl = $category->image;
        if ($request->hasFile('image')) {
            $this->imageUploadService->delete($category->image);
            $imageUrl = $this->imageUploadService->upload($request->file('image'), 'categories');
        }

        $category->update([
            'title' => $validated['title'],
            'name' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image' => $imageUrl,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->title}' updated successfully!");
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $this->imageUploadService->delete($category->image);
        $title = $category->title;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$title}' deleted successfully.");
    }
}
