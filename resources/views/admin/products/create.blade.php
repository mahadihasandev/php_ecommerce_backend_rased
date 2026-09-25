@extends('admin.layouts.app')

@section('title', 'Add New Product')
@section('page_title', 'Add New Catalog Product')
@section('page_subtitle', 'Upload images, set pricing, stock inventory, and product specifications')

@section('content')
<div class="max-w-5xl mx-auto" x-data="productCreator()">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- Main Product Card -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            
            <div class="border-b border-slate-800 pb-4">
                <h3 class="text-base font-bold text-white">Basic Product Details</h3>
                <p class="text-xs text-slate-400 mt-0.5">Primary information displayed on product pages and search cards.</p>
            </div>

            <!-- Name and Slug -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Product Title *</label>
                    <input type="text" name="name" x-model="name" required value="{{ old('name') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. Sony WH-1000XM5 Wireless Headphones">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">URL Slug (Auto-generated if left empty)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. sony-wh-1000xm5-wireless-headphones">
                </div>
            </div>

            <!-- Price, Discount, Stock, Variant -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Retail Price ($) *</label>
                    <input type="number" step="0.01" name="price" required value="{{ old('price', '299.99') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Discount Price ($)</label>
                    <input type="number" step="0.01" name="discount" value="{{ old('discount') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="Optional sale price">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Stock Inventory *</label>
                    <input type="number" name="stock" min="0" required value="{{ old('stock', 25) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Variant Keyword</label>
                    <input type="text" name="variant" value="{{ old('variant', 'gadget') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. gadget, audio">
                </div>
            </div>

            <!-- Brand and Categories -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-slate-800/80">
                <!-- Brand Selection -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Brand</label>
                    <select name="brand_id" class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status & Featured -->
                <div class="flex items-center gap-6 pt-6">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Promotional Badge</label>
                        <select name="status" class="px-4 py-2.5 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                            <option value="">None</option>
                            <option value="new" {{ old('status') === 'new' ? 'selected' : '' }}>New Arrival</option>
                            <option value="hot" {{ old('status') === 'hot' ? 'selected' : '' }}>Hot Deal</option>
                            <option value="sale" {{ old('status') === 'sale' ? 'selected' : '' }}>On Sale</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 pt-4">
                        <input type="checkbox" name="isFeatured" id="isFeatured" value="1" {{ old('isFeatured') ? 'checked' : '' }}
                               class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <label for="isFeatured" class="text-xs font-semibold text-slate-300 cursor-pointer">
                            Mark as Featured Item
                        </label>
                    </div>
                </div>
            </div>

            <!-- Categories Checkboxes -->
            <div class="pt-2 border-t border-slate-800/80">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Assign Store Categories</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    @foreach($categories as $category)
                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-950/40 border border-slate-800 hover:border-slate-700 cursor-pointer transition-colors">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                               class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-xs text-slate-300">{{ $category->title }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Image Gallery Upload Card -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-5">
            <div class="border-b border-slate-800 pb-4">
                <h3 class="text-base font-bold text-white">Product Images & Gallery</h3>
                <p class="text-xs text-slate-400 mt-0.5">Upload one or multiple photos. The first image will be used as the primary catalog thumbnail.</p>
            </div>

            <!-- Multi Image Dropzone -->
            <div class="relative border-2 border-dashed border-slate-700/80 hover:border-brand-500/80 rounded-3xl p-8 text-center cursor-pointer transition-colors bg-slate-950/40 group overflow-hidden">
                <input type="file" name="images[]" multiple accept="image/*" @change="handleFiles($event)"
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                
                <div class="space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                        <i data-lucide="image-plus" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <span class="text-sm font-bold text-white">Click or drag images here to upload</span>
                        <p class="text-xs text-slate-400 mt-1">Select multiple PNG, JPG, or WEBP images up to 5MB each</p>
                    </div>
                </div>
            </div>

            <!-- Image Previews Grid -->
            <div x-show="previewUrls.length > 0" class="pt-2">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Selected Images Preview (<span x-text="previewUrls.length"></span>)</div>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    <template x-for="(url, index) in previewUrls" :key="index">
                        <div class="relative rounded-2xl overflow-hidden aspect-square bg-slate-950 border border-slate-800 group">
                            <img :src="url" class="w-full h-full object-cover">
                            <span x-show="index === 0" class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-brand-600 text-white shadow">
                                Primary
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Specifications & Description Card -->
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            <div class="border-b border-slate-800 pb-4">
                <h3 class="text-base font-bold text-white">Key Features & Description</h3>
                <p class="text-xs text-slate-400 mt-0.5">Highlight bullet points and detailed description rendered on the Next.js frontend.</p>
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
                                   placeholder="e.g. Industry-leading Noise Cancellation with dual processors">
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
            <div class="pt-2 border-t border-slate-800/80">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Full Product Overview Description</label>
                <textarea name="description" rows="5"
                          class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                          placeholder="Provide in-depth details about the product's performance, build quality, and specifications...">{{ old('description') }}</textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-800">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-500/25 transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Publish Product</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function productCreator() {
        return {
            name: "{{ old('name', '') }}",
            features: [
                'Premium audio performance with crystal clarity',
                'Fast charging support up to 30 hours playback',
                'Ergonomic lightweight comfort design'
            ],
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
