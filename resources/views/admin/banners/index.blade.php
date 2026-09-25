@extends('admin.layouts.app')

@section('title', 'Banners & Promotions')
@section('page_title', 'Banners & Promotional Campaigns')
@section('page_subtitle', 'Upload and configure hero banners and promotional marketing cards')

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-900/60 p-6 rounded-3xl border border-slate-800/80 backdrop-blur-sm">
        <div>
            <h2 class="text-xl font-bold text-white">Active Promotional Banners</h2>
            <p class="text-xs text-slate-400 mt-0.5">These banners appear on the homepage slider and deals section of your storefront.</p>
        </div>
        <a href="{{ route('admin.banners.create') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-500/25 transition-all">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Upload New Banner</span>
        </a>
    </div>

    <!-- Banners Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($banners as $banner)
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl overflow-hidden shadow-xl hover:border-slate-700/80 transition-all flex flex-col group">
            
            <!-- Banner Image Preview -->
            <div class="relative h-48 w-full bg-slate-950 overflow-hidden">
                @if($banner->image)
                    <img src="{{ $banner->image }}" alt="{{ $banner->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <div class="w-full h-full flex items-center justify-center text-slate-500">
                        <i data-lucide="image" class="w-10 h-10"></i>
                    </div>
                @endif

                <!-- Overlay Badges -->
                <div class="absolute top-3 left-3 flex flex-wrap gap-2">
                    @if($banner->badge)
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-500 text-white shadow-md">
                            {{ $banner->badge }}
                        </span>
                    @endif
                    @if($banner->discountAmount)
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-500 text-black shadow-md">
                            {{ $banner->discountAmount }}% OFF
                        </span>
                    @endif
                </div>
            </div>

            <!-- Banner Info -->
            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                <div>
                    @if($banner->subtitle)
                        <span class="text-[11px] font-semibold text-brand-400 uppercase tracking-wider">{{ $banner->subtitle }}</span>
                    @endif
                    <h3 class="text-base font-bold text-white mt-1 group-hover:text-brand-300 transition-colors">{{ $banner->title }}</h3>
                    @if($banner->description)
                        <p class="text-xs text-slate-400 mt-1.5 line-clamp-2 leading-relaxed">{{ $banner->description }}</p>
                    @endif
                </div>

                @if($banner->link)
                <div class="text-[11px] text-slate-400 truncate flex items-center gap-1.5 pt-2 border-t border-slate-800/80">
                    <i data-lucide="link" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span class="truncate">{{ $banner->link }}</span>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-800/80">
                    <span class="text-[11px] text-slate-400">
                        By {{ $banner->user?->name ?? 'Admin' }}
                    </span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.banners.edit', $banner->id) }}" 
                           class="p-2 text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 transition-colors" title="Edit">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </a>
                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this banner?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center bg-slate-900/50 rounded-3xl border border-slate-800/80">
            <div class="w-16 h-16 rounded-2xl bg-slate-800/80 flex items-center justify-center mx-auto text-slate-500 mb-3">
                <i data-lucide="image" class="w-8 h-8"></i>
            </div>
            <h3 class="text-base font-bold text-white">No Banners Found</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Upload your first promotional banner to engage visitors on your Next.js storefront.</p>
            <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-brand-600 hover:bg-brand-500 transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Upload Banner</span>
            </a>
        </div>
        @endforelse
    </div>

    @if($banners->hasPages())
    <div class="pt-4">
        {{ $banners->links() }}
    </div>
    @endif
</div>
@endsection
