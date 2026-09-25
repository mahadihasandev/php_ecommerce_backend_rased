<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Models\Brand;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    public function index()
    {
        $brands = Brand::withCount('products')->latest()->paginate(15);
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(StoreBrandRequest $request)
    {
        $validated = $request->validated();

        $slug = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['title']);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $this->imageUploadService->upload($request->file('image'), 'brands');
        }

        $brand = Brand::create([
            'title' => $validated['title'],
            'name' => $validated['title'],
            'brandName' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image' => $imageUrl,
        ]);

        return redirect()->route('admin.brands.index')
            ->with('success', "Brand '{$brand->title}' created successfully!");
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:brands,slug,' . $id],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
        ]);

        $slug = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['title']);

        $imageUrl = $brand->image;
        if ($request->hasFile('image')) {
            $this->imageUploadService->delete($brand->image);
            $imageUrl = $this->imageUploadService->upload($request->file('image'), 'brands');
        }

        $brand->update([
            'title' => $validated['title'],
            'name' => $validated['title'],
            'brandName' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image' => $imageUrl,
        ]);

        return redirect()->route('admin.brands.index')
            ->with('success', "Brand '{$brand->title}' updated successfully!");
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $this->imageUploadService->delete($brand->image);
        $title = $brand->title;
        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', "Brand '{$title}' deleted successfully.");
    }
}
