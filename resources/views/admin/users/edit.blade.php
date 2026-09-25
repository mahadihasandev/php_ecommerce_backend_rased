@extends('admin.layouts.app')

@section('title', 'Configure Permissions: ' . $user->name)
@section('page_title', 'Configure User & Vendor Permissions')
@section('page_subtitle', 'Grant or revoke what ' . $user->name . ' is allowed to perform across the dashboard')

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ role: '{{ $user->role }}' }">
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
            
            <div class="border-b border-slate-800 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white">User Profile & Access Role</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Manage credentials, marketplace status, and role classification.</p>
                </div>
                <span class="text-xs font-mono text-slate-400">User ID #{{ $user->id }}</span>
            </div>

            <!-- Role & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">System Role *</label>
                    <select name="role" x-model="role" required
                            class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="vendor" {{ $user->role === 'vendor' ? 'selected' : '' }}>Vendor (Multi-Store Merchant)</option>
                        <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff / Moderator</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator (SuperAdmin)</option>
                        <option value="customer" {{ $user->role === 'customer' ? 'selected' : '' }}>Regular Customer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Account Status *</label>
                    <select name="status" required
                            class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active (Full Access Allowed)</option>
                        <option value="pending" {{ $user->status === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>Suspended (Portal Access Blocked)</option>
                    </select>
                </div>
            </div>

            <!-- Name and Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Full Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Email Address *</label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Password Reset & Phone -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">New Password (Leave blank to keep current)</label>
                    <input type="password" name="password"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="Enter new password if changing">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Vendor Store Information -->
            <div x-show="role === 'vendor'" x-transition class="space-y-4 pt-4 border-t border-slate-800/80">
                <div class="flex items-center gap-2 text-indigo-400 text-xs font-bold uppercase tracking-wider">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>Vendor Merchant Profile</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Store / Brand Name</label>
                    <input type="text" name="store_name" value="{{ old('store_name', $user->store_name) }}"
                           class="w-full px-4 py-3 bg-slate-950/60 border border-indigo-900/40 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                           placeholder="e.g. Apex Tech Store">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Store Description</label>
                    <textarea name="store_description" rows="2"
                              class="w-full px-4 py-3 bg-slate-950/60 border border-indigo-900/40 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ old('store_description', $user->store_description) }}</textarea>
                </div>
            </div>

            <!-- Permissions Matrix -->
            @php
                $userPermissions = is_array($user->permissions) ? $user->permissions : [];
            @endphp
            <div x-show="role !== 'admin'" x-transition class="pt-4 border-t border-slate-800/80 space-y-4">
                <div>
                    <h4 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                        <span>Granular Permissions (What this user can or cannot do)</span>
                    </h4>
                    <p class="text-xs text-slate-400 mt-0.5">Check the boxes for actions this user is permitted to perform.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($availablePermissions as $permKey => $permLabel)
                    <label class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-950/50 border border-slate-800 hover:border-slate-700 cursor-pointer transition-all">
                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                               {{ in_array($permKey, old('permissions', $userPermissions)) ? 'checked' : '' }}
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
                SuperAdmin accounts automatically retain all permissions.
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-800">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-500/25 transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Update Permissions</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
