<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminAuthController extends Controller
{
    /**
     * Show admin & vendor login form.
     */
    public function showLogin()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'vendor', 'staff'], true)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Process login authentication.
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $remember = $request->boolean('remember');

            if (Auth::attempt($credentials, $remember)) {
                $user = Auth::user();

                if ($user->status === 'suspended') {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'This account has been suspended. Please contact support.',
                    ])->onlyInput('email');
                }

                if (!in_array($user->role, ['admin', 'vendor', 'staff'], true)) {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'Access denied. You do not have vendor or administrator privileges.',
                    ])->onlyInput('email');
                }

                $request->session()->regenerate();

                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', "Welcome back, {$user->name}!");
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Admin Login Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return back()->withErrors([
                'email' => 'System error: ' . $e->getMessage(),
            ])->onlyInput('email');
        }
    }

    /**
     * Show registration portal for admin / vendor.
     */
    public function showRegister()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'vendor', 'staff'], true)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.register');
    }

    /**
     * Handle registration for new vendor/admin.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'account_type' => ['required', 'string', 'in:vendor,admin'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $role = $validated['account_type'];
        
        // Assign initial default permissions based on role
        $permissions = $role === 'admin' 
            ? array_keys(User::PERMISSIONS) 
            : ['manage_products', 'manage_orders'];

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'phone' => $validated['phone'] ?? null,
            'store_name' => $role === 'vendor' ? ($validated['store_name'] ?? $validated['name'] . "'s Store") : null,
            'store_description' => $validated['store_description'] ?? null,
            'permissions' => $permissions,
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('success', "Account created successfully! Welcome to your dashboard, {$user->name}.");
    }

    /**
     * Log the admin/vendor out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been successfully logged out.');
    }
}
