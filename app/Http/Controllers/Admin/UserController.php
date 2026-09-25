<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users, vendors, and staff.
     */
    public function index(Request $request)
    {
        $role = $request->input('role');
        $query = User::withCount('products')->latest();

        if ($role && in_array($role, ['admin', 'vendor', 'staff', 'customer'], true)) {
            $query->where('role', $role);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('store_name', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        $roleCounts = \Illuminate\Support\Facades\Cache::remember('admin_user_role_counts', 60, function () {
            return [
                'all' => User::count(),
                'admin' => User::where('role', 'admin')->count(),
                'vendor' => User::where('role', 'vendor')->count(),
                'staff' => User::where('role', 'staff')->count(),
                'customer' => User::where('role', 'customer')->count(),
            ];
        });

        return view('admin.users.index', compact('users', 'roleCounts', 'role'));
    }

    /**
     * Show form to create new vendor or staff user from inside dashboard.
     */
    public function create()
    {
        $availablePermissions = User::PERMISSIONS;
        return view('admin.users.create', compact('availablePermissions'));
    }

    /**
     * Store new user created from inside dashboard.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(6)],
            'role' => ['required', 'string', 'in:admin,vendor,staff,customer'],
            'status' => ['required', 'string', 'in:active,pending,suspended'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(User::PERMISSIONS))],
        ]);

        $permissions = $validated['role'] === 'admin' 
            ? array_keys(User::PERMISSIONS) 
            : ($validated['permissions'] ?? []);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
            'phone' => $validated['phone'] ?? null,
            'store_name' => $validated['store_name'] ?? null,
            'store_description' => $validated['store_description'] ?? null,
            'permissions' => $permissions,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' created successfully with assigned permissions!");
    }

    /**
     * Show the user edit and permissions configuration page.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $availablePermissions = User::PERMISSIONS;

        return view('admin.users.edit', compact('user', 'availablePermissions'));
    }

    /**
     * Update user role, status, store details, and granular permissions.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['required', 'string', 'in:admin,vendor,staff,customer'],
            'status' => ['required', 'string', 'in:active,pending,suspended'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', Password::min(6)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(User::PERMISSIONS))],
        ]);

        // Safety check: Prevent admin from de-admining or suspending themselves
        if ($user->id === $currentUser->id && ($validated['role'] !== 'admin' || $validated['status'] !== 'active')) {
            return back()->with('error', 'You cannot change your own role or suspend your own admin account.');
        }

        $permissions = $validated['role'] === 'admin' 
            ? array_keys(User::PERMISSIONS) 
            : ($validated['permissions'] ?? []);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'phone' => $validated['phone'] ?? null,
            'store_name' => $validated['role'] === 'vendor' ? ($validated['store_name'] ?? null) : null,
            'store_description' => $validated['role'] === 'vendor' ? ($validated['store_description'] ?? null) : null,
            'permissions' => $permissions,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', "Permissions and profile for '{$user->name}' updated successfully!");
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$name}' deleted successfully.");
    }
}
