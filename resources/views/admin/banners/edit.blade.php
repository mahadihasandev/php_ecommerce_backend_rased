@extends('admin.layouts.app')

@section('title', 'Edit Banner: ' . $banner->title)
@section('page_title', 'Edit Promotional Banner')
@section('page_subtitle', 'Update banner media, badges, discount, or destination link')

@section('content')
<div class="max-w-4xl mx-auto" x-data="bannerEditor()">
    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            
            <div class="border-b border-slate-800 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white">Edit Banner Details</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Modify fields or upload a replacement image file.</p>
                </div>
                <span class="text-xs text-slate-400">ID: #{{ $banner->id }}</span>
            </div>

            <!-- Upload Area & Instant Live Preview -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Banner Image</label>
                
                <div class="relative border-2 border-dashed border-slate-700/80 hover:border-brand-500/80 rounded-3xl p-6 text-center cursor-pointer transition-colors bg-slate-950/40 group overflow-hidden">
                    <input type="file" name="image" id="bannerImage" accept="image/*" @change="handleFileSelect($event)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                    
                    <div class="relative rounded-2xl overflow-hidden h-64 w-full bg-slate-950">
                        <img :src="imagePreview" class="w-full h-full object-cover">
                        
                        <!-- Overlay Preview -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent p-6 flex flex-col justify-end text-left">
                            <div class="flex gap-2 mb-2">
                                <span x-show="badge" x-text="badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-500 text-white"></span>
                                <span x-show="discount" x-text="discount + '% OFF'" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-500 text-black"></span>
                            </div>
                            <span x-show="subtitle" x-text="subtitle" class="text-xs font-semibold text-brand-300 uppercase tracking-wider"></span>
                            <h4 x-text="title || 'Banner Headline'" class="text-xl font-extrabold text-white mt-0.5"></h4>
                        </div>

                        <span class="absolute top-3 right-3 z-30 px-3 py-1 rounded-lg bg-slate-900/80 backdrop-blur-md text-xs font-medium text-slate-300">
                            Click to replace image
                        </span>
                    </div>
                </div>
            </div>

            <!-- Fields Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <!-- Title -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Headline Title *</label>
                    <input type="text" name="title" x-model="title" required
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <!-- Subtitle -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Sub-title / Tagline</label>
                    <input type="text" name="subtitle" x-model="subtitle"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <!-- Badge -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Badge Text</label>
                    <input type="text" name="badge" x-model="badge"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <!-- Discount Amount -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Discount Percentage (%)</label>
                    <input type="number" name="discountAmount" x-model="discount" min="0" max="100"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <!-- Link -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Target Destination Link</label>
                    <input type="text" name="link" value="{{ old('link', $banner->link) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Campaign Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ old('description', $banner->description) }}</textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-800">
                <a href="{{ route('admin.banners.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
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
    function bannerEditor() {
        return {
            title: @json($banner->title),
            subtitle: @json($banner->subtitle ?? ''),
            badge: @json($banner->badge ?? ''),
            discount: @json($banner->discountAmount ?? ''),
            imagePreview: @json($banner->image ?? '/fallback-product.svg'),
            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.imagePreview = URL.createObjectURL(file);
                }
            }
        };
    }
</script>
@endpush
