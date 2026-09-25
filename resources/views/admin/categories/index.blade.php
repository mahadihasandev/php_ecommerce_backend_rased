@extends('admin.layouts.app')

@section('title', 'Categories Catalog')
@section('page_title', 'Product Categories')
@section('page_subtitle', 'Organize products into intuitive collections and navigational tiers')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-900/60 p-6 rounded-3xl border border-slate-800/80 backdrop-blur-sm">
        <div>
            <h2 class="text-xl font-bold text-white">Store Categories</h2>
            <p class="text-xs text-slate-400 mt-0.5">Categorize your items so shoppers can filter easily on Next.js.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-500/25 transition-all">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Add New Category</span>
        </a>
    </div>

    <!-- Category Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($categories as $category)
        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl overflow-hidden p-5 shadow-xl hover:border-slate-700/80 transition-all flex flex-col justify-between group">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-800 overflow-hidden flex-shrink-0 border border-slate-700/60">
                    @if($category->image)
                        <img src="{{ $category->image }}" alt="{{ $category->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-500">
                            <i data-lucide="folder" class="w-6 h-6"></i>
                        </div>
                    @endif
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-white truncate group-hover:text-brand-300 transition-colors">{{ $category->title }}</h3>
                    <div class="text-[11px] font-mono text-slate-400 truncate">{{ $category->slug_text }}</div>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                        {{ $category->products_count }} Products
                    </span>
                </div>
            </div>

            @if($category->description)
                <p class="text-xs text-slate-400 mt-3 line-clamp-2">{{ $category->description }}</p>
            @endif

            <div class="flex items-center justify-end gap-2 pt-4 mt-4 border-t border-slate-800/80">
                <a href="{{ route('admin.categories.edit', $category->id) }}" 
                   class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </a>
                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" 
                      onsubmit="return confirm('Delete this category? Associated products will be detached.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-500 italic">No categories created yet.</div>
        @endforelse
    </div>
</div>
@endsection
