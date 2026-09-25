<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Product::with(['brand', 'categories', 'user']);

        // Multi-vendor scoping: vendors see only their products
        if ($user->isVendor()) {
            $query->where('user_id', $user->id);
        }

        // Search by keyword
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        // Filter by stock status
        if ($request->input('stock_status') === 'low') {
            $query->where('stock', '<=', 5);
        } elseif ($request->input('stock_status') === 'out') {
            $query->where('stock', '<=', 0);
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::orderBy('title')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::orderBy('title')->get();
        $brands = Brand::orderBy('title')->get();

        return view('admin.products.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        // 1. Generate unique slug if not provided
        $slug = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['name']);

        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        // 2. Upload images
        $imageUrls = [];
        if ($request->hasFile('images')) {
            $imageUrls = $this->imageUploadService->uploadMultiple($request->file('images'), 'products');
        }

        // 3. Process key features
        $keyfeatures = [];
        if (!empty($validated['keyfeature'])) {
            $keyfeatures = array_values(array_filter(array_map('trim', (array) $validated['keyfeature'])));
        }

        // 4. Create product
        $product = Product::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'price' => $validated['price'],
            'discount' => $validated['discount'] ?? null,
            'stock' => $validated['stock'],
            'status' => $validated['status'] ?? null,
            'variant' => $validated['variant'] ?? null,
            'isFeatured' => $request->boolean('isFeatured'),
            'brand_id' => $validated['brand_id'] ?? null,
            'images' => $imageUrls,
            'keyfeature' => $keyfeatures,
            'description' => $validated['description'] ?? null,
        ]);

        // 5. Attach categories
        if (!empty($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' created successfully!");
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        $product = Product::with(['categories'])->findOrFail($id);
        $user = Auth::user();

        // Authorization check: vendors can only edit their own products
        if ($user->isVendor() && $product->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this product.');
        }

        $categories = Category::orderBy('title')->get();
        $brands = Brand::orderBy('title')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $user = Auth::user();

        if ($user->isVendor() && $product->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this product.');
        }

        $validated = $request->validated();

        // Handle slug
        $slug = !empty($validated['slug']) 
            ? Str::slug($validated['slug']) 
            : Str::slug($validated['name']);

        // Merge existing retained images with any newly uploaded images
        $existingImages = $request->input('existing_images', []);
        $newImages = [];
        if ($request->hasFile('images')) {
            $newImages = $this->imageUploadService->uploadMultiple($request->file('images'), 'products');
        }
        $finalImages = array_values(array_merge((array) $existingImages, $newImages));

        // Delete discarded images
        $currentImages = is_array($product->images) ? $product->images : [];
        $deletedImages = array_diff($currentImages, (array) $existingImages);
        $this->imageUploadService->deleteMultiple($deletedImages);

        // Key features
        $keyfeatures = [];
        if (!empty($validated['keyfeature'])) {
            $keyfeatures = array_values(array_filter(array_map('trim', (array) $validated['keyfeature'])));
        }

        $product->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'price' => $validated['price'],
            'discount' => $validated['discount'] ?? null,
            'stock' => $validated['stock'],
            'status' => $validated['status'] ?? null,
            'variant' => $validated['variant'] ?? null,
            'isFeatured' => $request->boolean('isFeatured'),
            'brand_id' => $validated['brand_id'] ?? null,
            'images' => $finalImages,
            'keyfeature' => $keyfeatures,
            'description' => $validated['description'] ?? null,
        ]);

        // Sync categories
        $product->categories()->sync($validated['category_ids'] ?? []);

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' updated successfully!");
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $user = Auth::user();

        if ($user->isVendor() && $product->user_id !== $user->id) {
            abort(403, 'Unauthorized to delete this product.');
        }

        // Delete images
        $this->imageUploadService->deleteMultiple($product->images);

        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$name}' deleted successfully.");
    }
}
