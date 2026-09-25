@extends('admin.layouts.app')

@section('title', 'Users & Permissions')
@section('page_title', 'Multi-Vendor & User Access Control')
@section('page_subtitle', 'Manage merchant vendors, assign roles, and grant or revoke specific granular permissions')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Search Header -->
    <div class="bg-slate-900/60 p-6 rounded-3xl border border-slate-800/80 backdrop-blur-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white">Users & Vendor Directory</h2>
                <p class="text-xs text-slate-400 mt-0.5">Control who can upload products, manage banners, or process customer orders.</p>
            </div>
            
            <a href="{{ route('admin.users.create') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-500/25 transition-all">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Register New User / Vendor</span>
            </a>
        </div>

        <!-- Role Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pt-2 border-t border-slate-800/80 scrollbar-none">
            @foreach(['all' => 'All Users', 'admin' => 'Administrators', 'vendor' => 'Vendors', 'staff' => 'Staff', 'customer' => 'Customers'] as $key => $label)
            <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['role' => $key === 'all' ? null : $key])) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-1.5
                   {{ (request('role') === $key || (empty(request('role')) && $key === 'all')) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'bg-slate-950/40 text-slate-400 hover:text-white border border-slate-800' }}">
                <span>{{ $label }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/15">
                    {{ $roleCounts[$key] ?? 0 }}
                </span>
            </a>
            @endforeach
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-3xl overflow-hidden shadow-xl backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 border-b border-slate-800 uppercase tracking-wider font-semibold">
                        <th class="py-4 px-6">User & Store</th>
                        <th class="py-4 px-4">Role</th>
                        <th class="py-4 px-4">Account Status</th>
                        <th class="py-4 px-4">Catalog Products</th>
                        <th class="py-4 px-4">Active Permissions</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <!-- User Name & Store -->
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow text-xs">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-white truncate">{{ $user->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                                    @if($user->store_name)
                                        <div class="text-[10px] font-semibold text-indigo-400 mt-0.5 flex items-center gap-1">
                                            <i data-lucide="store" class="w-3 h-3"></i>
                                            <span>{{ $user->store_name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Role Pill -->
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $user->role === 'admin' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 
                                  ($user->role === 'vendor' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 
                                  ($user->role === 'staff' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' :
                                  'bg-slate-800 text-slate-400 border border-slate-700/50')) }}">
                                {{ $user->role }}
                            </span>
                        </td>

                        <!-- Account Status -->
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold
                                {{ $user->status === 'active' ? 'text-emerald-400' : ($user->status === 'pending' ? 'text-amber-400' : 'text-rose-400') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->status === 'active' ? 'bg-emerald-400' : ($user->status === 'pending' ? 'bg-amber-400' : 'bg-rose-400') }}"></span>
                                {{ ucfirst($user->status ?: 'active') }}
                            </span>
                        </td>

                        <!-- Products Count -->
                        <td class="py-4 px-4">
                            <span class="text-slate-300 font-semibold">{{ $user->products_count }} items</span>
                        </td>

                        <!-- Permissions Badges -->
                        <td class="py-4 px-4">
                            @if($user->role === 'admin')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-300 border border-purple-500/30">
                                    Full SuperAdmin Access
                                </span>
                            @else
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @if(is_array($user->permissions) && count($user->permissions) > 0)
                                        @foreach($user->permissions as $perm)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-slate-800 text-slate-300 border border-slate-700/60">
                                                {{ str_replace('manage_', '', $perm) }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-slate-500 italic text-[11px]">No active permissions</span>
                                    @endif
                                </div>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-medium text-xs transition-colors border border-slate-700/60">
                                    <i data-lucide="shield" class="w-3.5 h-3.5 text-brand-400"></i>
                                    <span>Permissions</span>
                                </a>

                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" 
                                      onsubmit="return confirm('Delete this user? All their products will remain but become unassigned.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition-colors" title="Delete">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500 italic">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-800/80">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
