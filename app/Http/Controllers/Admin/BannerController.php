<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    /**
     * Display a listing of banners.
     */
    public function index()
    {
        $banners = Banner::with('user')->latest()->paginate(10);
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created banner in storage.
     */
    public function store(StoreBannerRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $this->imageUploadService->upload($request->file('image'), 'banners');
        }

        $banner = Banner::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'description' => $validated['description'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'discountAmount' => $validated['discountAmount'] ?? null,
            'link' => $validated['link'] ?? null,
            'image' => $imageUrl,
        ]);

        return redirect()->route('admin.banners.index')
            ->with('success', "Banner '{$banner->title}' uploaded successfully!");
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified banner in storage.
     */
    public function update(UpdateBannerRequest $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $validated = $request->validated();

        $imageUrl = $banner->image;
        if ($request->hasFile('image')) {
            // Remove old image
            $this->imageUploadService->delete($banner->image);
            $imageUrl = $this->imageUploadService->upload($request->file('image'), 'banners');
        }

        $banner->update([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'description' => $validated['description'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'discountAmount' => $validated['discountAmount'] ?? null,
            'link' => $validated['link'] ?? null,
            'image' => $imageUrl,
        ]);

        return redirect()->route('admin.banners.index')
            ->with('success', "Banner '{$banner->title}' updated successfully!");
    }

    /**
     * Remove the specified banner from storage.
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $this->imageUploadService->delete($banner->image);
        $title = $banner->title;
        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', "Banner '{$title}' removed successfully.");
    }
}
