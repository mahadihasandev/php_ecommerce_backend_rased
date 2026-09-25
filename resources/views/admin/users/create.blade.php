@extends('admin.layouts.app')

@section('title', 'Register User & Permissions')
@section('page_title', 'Create User / Vendor Account')
@section('page_subtitle', 'Register a new store vendor or administrator with custom permission grants')

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ role: 'vendor' }">
    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            
            <div class="border-b border-slate-800 pb-4">
                <h3 class="text-base font-bold text-white">Account Information</h3>
                <p class="text-xs text-slate-400 mt-0.5">Basic profile credentials for the user.</p>
            </div>

            <!-- Role & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">System Role *</label>
                    <select name="role" x-model="role" required
                            class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="vendor">Vendor (Multi-Store Merchant)</option>
                        <option value="staff">Staff / Moderator</option>
                        <option value="admin">Administrator (SuperAdmin)</option>
                        <option value="customer">Regular Customer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Account Status *</label>
                    <select name="status" required
                            class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="active">Active (Access Allowed)</option>
                        <option value="pending">Pending Approval</option>
                        <option value="suspended">Suspended (Access Blocked)</option>
                    </select>
                </div>
            </div>

            <!-- Name and Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Full Name *</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. John Doe">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Email Address *</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="john@example.com">
                </div>
            </div>

            <!-- Password and Phone -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Initial Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="Min 6 characters">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="+1 (555) 000-0000">
                </div>
            </div>

            <!-- Vendor Store Information (Shown if role === 'vendor') -->
            <div x-show="role === 'vendor'" x-transition class="space-y-4 pt-4 border-t border-slate-800/80">
                <div class="flex items-center gap-2 text-indigo-400 text-xs font-bold uppercase tracking-wider">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>Vendor Store Configuration</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Store / Merchant Name</label>
                    <input type="text" name="store_name" value="{{ old('store_name') }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-indigo-900/40 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. Nexus Electronics Store">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Store Description</label>
                    <textarea name="store_description" rows="2"
                              class="w-full px-4 py-3 bg-slate-950/60 border border-indigo-900/40 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                              placeholder="Brief description of the merchant's store...">{{ old('store_description') }}</textarea>
                </div>
            </div>

            <!-- Granular Permissions Matrix -->
            <div x-show="role !== 'admin'" x-transition class="pt-4 border-t border-slate-800/80 space-y-4">
                <div>
                    <h4 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                        <span>Granular Permissions (What this user can or cannot do)</span>
                    </h4>
                    <p class="text-xs text-slate-400 mt-0.5">Toggle specific capabilities granted to this user.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($availablePermissions as $permKey => $permLabel)
                    <label class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-950/50 border border-slate-800 hover:border-slate-700 cursor-pointer transition-all">
                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                               {{ in_array($permKey, ['manage_products', 'manage_orders']) ? 'checked' : '' }}
                               class="w-4 h-4 mt-0.5 rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="text-xs font-bold text-white block">{{ $permLabel }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $permKey }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div x-show="role === 'admin'" class="p-4 rounded-2xl bg-purple-500/10 border border-purple-500/30 text-purple-300 text-xs">
                <i data-lucide="info" class="w-4 h-4 inline mr-1 text-purple-400"></i>
                Administrators automatically have full unrestricted access to all features and permissions.
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-800">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-500/25 transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Create User with Permissions</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
