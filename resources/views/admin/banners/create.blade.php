@extends('admin.layouts.app')

@section('title', 'Upload Promotional Banner')
@section('page_title', 'Upload Promotional Banner')
@section('page_subtitle', 'Create a new hero or campaign banner with live preview')

@section('content')
<div class="max-w-4xl mx-auto" x-data="bannerUploader()">
    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            
            <div class="border-b border-slate-800 pb-4">
                <h3 class="text-base font-bold text-white">Banner Media & Visual Preview</h3>
                <p class="text-xs text-slate-400 mt-0.5">Upload a high-resolution banner image (recommended: 1920x600 or 1200x500).</p>
            </div>

            <!-- Upload Area & Instant Live Preview -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Banner Image File *</label>
                
                <!-- Dropzone / Picker -->
                <div class="relative border-2 border-dashed border-slate-700/80 hover:border-brand-500/80 rounded-3xl p-6 text-center cursor-pointer transition-colors bg-slate-950/40 group overflow-hidden">
                    <input type="file" name="image" id="bannerImage" accept="image/*" required @change="handleFileSelect($event)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                    
                    <template x-if="!imagePreview">
                        <div class="space-y-3 py-6">
                            <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                                <i data-lucide="upload-cloud" class="w-7 h-7"></i>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-white">Click or drag image file here to upload</span>
                                <p class="text-xs text-slate-400 mt-1">PNG, JPG, WEBP or SVG up to 5MB</p>
                            </div>
                        </div>
                    </template>

                    <!-- Live Image Preview -->
                    <template x-if="imagePreview">
                        <div class="relative rounded-2xl overflow-hidden h-64 w-full bg-slate-950">
                            <img :src="imagePreview" class="w-full h-full object-cover">
                            
                            <!-- Live Banner Overlay Preview -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent p-6 flex flex-col justify-end text-left">
                                <div class="flex gap-2 mb-2">
                                    <span x-show="badge" x-text="badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-500 text-white"></span>
                                    <span x-show="discount" x-text="discount + '% OFF'" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-500 text-black"></span>
                                </div>
                                <span x-show="subtitle" x-text="subtitle" class="text-xs font-semibold text-brand-300 uppercase tracking-wider"></span>
                                <h4 x-text="title || 'Banner Headline Preview'" class="text-xl font-extrabold text-white mt-0.5"></h4>
                            </div>

                            <span class="absolute top-3 right-3 z-30 px-3 py-1 rounded-lg bg-slate-900/80 backdrop-blur-md text-xs font-medium text-slate-300">
                                Click to replace image
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Fields Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <!-- Title -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Headline Title *</label>
                    <input type="text" name="title" x-model="title" required value="{{ old('title') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. Next-Gen Wireless Audio Experience">
                </div>

                <!-- Subtitle -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Sub-title / Tagline</label>
                    <input type="text" name="subtitle" x-model="subtitle" value="{{ old('subtitle') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. Limited Summer Collection">
                </div>

                <!-- Badge -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Badge Text</label>
                    <input type="text" name="badge" x-model="badge" value="{{ old('badge', 'HOT DEAL') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. HOT DEAL, NEW ARRIVAL">
                </div>

                <!-- Discount Amount -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Discount Percentage (%)</label>
                    <input type="number" name="discountAmount" x-model="discount" min="0" max="100" value="{{ old('discountAmount', 25) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. 25">
                </div>

                <!-- Link -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Target Destination Link</label>
                    <input type="text" name="link" value="{{ old('link', '/shop') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. /shop or /product/headphones">
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Campaign Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                              placeholder="Describe promotional terms or special offer details...">{{ old('description') }}</textarea>
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
                    <span>Publish Banner</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function bannerUploader() {
        return {
            title: "{{ old('title', '') }}",
            subtitle: "{{ old('subtitle', '') }}",
            badge: "{{ old('badge', 'HOT DEAL') }}",
            discount: "{{ old('discountAmount', '25') }}",
            imagePreview: null,
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
