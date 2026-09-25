@extends('admin.layouts.app')

@section('title', 'Edit Product: ' . $product->name)
@section('page_title', 'Edit Catalog Product')
@section('page_subtitle', 'Update product details, gallery images, pricing, and specifications')

@section('content')
<div class="max-w-5xl mx-auto" x-data="productEditor()">
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Main Product Card -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            
            <div class="border-b border-slate-800 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white">Basic Product Details</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Primary information displayed on product pages.</p>
                </div>
                <span class="text-xs text-slate-400">ID: #{{ $product->id }}</span>
            </div>

            <!-- Name and Slug -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Product Title *</label>
                    <input type="text" name="name" x-model="name" required
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">URL Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $product->slug_text) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Price, Discount, Stock, Variant -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Retail Price ($) *</label>
                    <input type="number" step="0.01" name="price" required value="{{ old('price', $product->price) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Discount Price ($)</label>
                    <input type="number" step="0.01" name="discount" value="{{ old('discount', $product->discount) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Stock Inventory *</label>
                    <input type="number" name="stock" min="0" required value="{{ old('stock', $product->stock) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Variant Keyword</label>
                    <input type="text" name="variant" value="{{ old('variant', $product->variant) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Brand and Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-slate-800/80">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Brand</label>
                    <select name="brand_id" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-6 pt-6">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Promotional Badge</label>
                        <select name="status" class="px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                            <option value="">None</option>
                            <option value="new" {{ old('status', $product->status) === 'new' ? 'selected' : '' }}>New Arrival</option>
                            <option value="hot" {{ old('status', $product->status) === 'hot' ? 'selected' : '' }}>Hot Deal</option>
                            <option value="sale" {{ old('status', $product->status) === 'sale' ? 'selected' : '' }}>On Sale</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 pt-4">
                        <input type="checkbox" name="isFeatured" id="isFeatured" value="1" {{ old('isFeatured', $product->isFeatured) ? 'checked' : '' }}
                               class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <label for="isFeatured" class="text-xs font-semibold text-slate-300 cursor-pointer">
                            Mark as Featured Item
                        </label>
                    </div>
                </div>
            </div>

            <!-- Categories Checkboxes -->
            @php
                $assignedCategoryIds = $product->categories->pluck('id')->toArray();
            @endphp
            <div class="pt-2 border-t border-slate-800/80">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Assign Store Categories</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    @foreach($categories as $category)
                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-950/40 border border-slate-800 hover:border-slate-700 cursor-pointer transition-colors">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                               {{ in_array($category->id, old('category_ids', $assignedCategoryIds)) ? 'checked' : '' }}
                               class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-xs text-slate-300">{{ $category->title }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Image Gallery Management Card -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-5">
            <div class="border-b border-slate-800 pb-4">
                <h3 class="text-base font-bold text-white">Product Gallery Images</h3>
                <p class="text-xs text-slate-400 mt-0.5">Manage existing photos or add new ones to your gallery.</p>
            </div>

            <!-- Existing Images Preview -->
            @if(is_array($product->images) && count($product->images) > 0)
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Current Images (Uncheck to delete on save)</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    @foreach($product->images as $img)
                    <label class="relative rounded-2xl overflow-hidden aspect-square bg-slate-950 border border-slate-800 group cursor-pointer block">
                        <img src="{{ $img }}" class="w-full h-full object-cover">
                        <div class="absolute bottom-0 inset-x-0 bg-slate-950/80 backdrop-blur-sm p-1.5 flex items-center justify-between text-[10px]">
                            <span class="text-slate-300 font-semibold">Keep</span>
                            <input type="checkbox" name="existing_images[]" value="{{ $img }}" checked
                                   class="w-3.5 h-3.5 rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Multi Image Dropzone for Uploading More -->
            <div class="relative border-2 border-dashed border-slate-700/80 hover:border-brand-500/80 rounded-3xl p-8 text-center cursor-pointer transition-colors bg-slate-950/40 group overflow-hidden">
                <input type="file" name="images[]" multiple accept="image/*" @change="handleFiles($event)"
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                
                <div class="space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                        <i data-lucide="image-plus" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <span class="text-sm font-bold text-white">Click or drag images here to add more photos</span>
                        <p class="text-xs text-slate-400 mt-1">PNG, JPG, or WEBP up to 5MB each</p>
                    </div>
                </div>
            </div>

            <!-- New Image Previews Grid -->
            <div x-show="previewUrls.length > 0" class="pt-2">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">New Images to Upload (<span x-text="previewUrls.length"></span>)</div>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    <template x-for="(url, index) in previewUrls" :key="index">
                        <div class="relative rounded-2xl overflow-hidden aspect-square bg-slate-950 border border-slate-800">
                            <img :src="url" class="w-full h-full object-cover">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Specifications & Description Card -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            <div class="border-b border-slate-800 pb-4">
                <h3 class="text-base font-bold text-white">Key Features & Description</h3>
                <p class="text-xs text-slate-400 mt-0.5">Feature bullet points and rich description.</p>
            </div>

            <!-- Dynamic Key Features Builder -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Key Feature Bullet Points</label>
                    <button type="button" @click="addFeature()" 
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-400 hover:text-brand-300">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add Bullet Point</span>
                    </button>
                </div>

                <div class="space-y-2.5">
                    <template x-for="(feature, index) in features" :key="index">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-mono" x-text="index + 1"></span>
                            <input type="text" name="keyfeature[]" x-model="features[index]"
                                   class="flex-1 px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                                   placeholder="Enter bullet point...">
                            <button type="button" @click="removeFeature(index)" title="Delete bullet point"
                                    class="p-2 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-rose-500/10 transition-colors shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Description -->
            @php
                $descText = is_string($product->description) ? $product->description : '';
            @endphp
            <div class="pt-2 border-t border-slate-800/80">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Full Product Overview Description</label>
                <textarea name="description" rows="5"
                          class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">{{ old('description', $descText) }}</textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-800">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-500/25 transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function productEditor() {
        let initialFeatures = @json($product->keyfeature ?? []);
        if (typeof initialFeatures === 'string') {
            try {
                const parsed = JSON.parse(initialFeatures);
                initialFeatures = Array.isArray(parsed) ? parsed : [initialFeatures];
            } catch (e) {
                initialFeatures = initialFeatures.split(/(?<=[.!?])\s+|\n|•/).map(s => s.trim()).filter(Boolean);
            }
        }
        if (!Array.isArray(initialFeatures)) {
            initialFeatures = [];
        }
        if (initialFeatures.length === 0) {
            initialFeatures = [''];
        }

        return {
            name: @json($product->name),
            features: initialFeatures,
            previewUrls: [],
            addFeature() {
                this.features.push('');
            },
            removeFeature(index) {
                this.features.splice(index, 1);
                if (this.features.length === 0) {
                    this.features.push('');
                }
            },
            handleFiles(event) {
                this.previewUrls = [];
                const files = event.target.files;
                if (files) {
                    for (let i = 0; i < files.length; i++) {
                        this.previewUrls.push(URL.createObjectURL(files[i]));
                    }
                }
            }
        };
    }
</script>
@endpush
