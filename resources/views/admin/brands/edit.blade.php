@extends('admin.layouts.app')

@section('title', 'Edit Brand: ' . $brand->title)
@section('page_title', 'Edit Brand')
@section('page_subtitle', 'Update brand title, slug, or logo')

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-5">
            <div class="border-b border-slate-800 pb-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Edit Brand Details</h3>
                <span class="text-xs text-slate-400">ID: #{{ $brand->id }}</span>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Brand Title *</label>
                <input type="text" name="title" required value="{{ old('title', $brand->title) }}"
                       class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <!-- Slug -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $brand->slug_text) }}"
                       class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <!-- Current Logo & Replacement -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Brand Logo</label>
                @if($brand->image)
                    <div class="flex items-center gap-4 mb-3 p-3 rounded-2xl bg-slate-950/50 border border-slate-800">
                        <img src="{{ $brand->image }}" alt="{{ $brand->title }}" class="w-12 h-12 object-contain">
                        <span class="text-xs text-slate-400">Current logo</span>
                    </div>
                @endif
                <input type="file" name="image" accept="image/*"
                       class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">About Brand</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ old('description', $brand->description) }}</textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-800">
                <a href="{{ route('admin.brands.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
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
