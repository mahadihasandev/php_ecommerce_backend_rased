@extends('admin.layouts.app')

@section('title', 'Edit Category: ' . $category->title)
@section('page_title', 'Edit Category')
@section('page_subtitle', 'Update category title, slug, thumbnail, or description')

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-5">
            <div class="border-b border-slate-800 pb-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Edit Category Details</h3>
                <span class="text-xs text-slate-400">ID: #{{ $category->id }}</span>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Category Name *</label>
                <input type="text" name="title" required value="{{ old('title', $category->title) }}"
                       class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <!-- Slug -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug_text) }}"
                       class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <!-- Current Image & Replacement -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Category Thumbnail Image</label>
                @if($category->image)
                    <div class="flex items-center gap-4 mb-3 p-3 rounded-2xl bg-slate-950/50 border border-slate-800">
                        <img src="{{ $category->image }}" alt="{{ $category->title }}" class="w-14 h-14 rounded-xl object-cover">
                        <span class="text-xs text-slate-400">Current image displayed on catalog</span>
                    </div>
                @endif
                <input type="file" name="image" accept="image/*"
                       class="w-full text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ old('description', $category->description) }}</textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-800">
                <a href="{{ route('admin.categories.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
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
