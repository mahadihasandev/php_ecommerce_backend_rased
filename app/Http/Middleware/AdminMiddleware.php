<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the Dashboard.');
        }

        $user = Auth::user();

        // Check if account is suspended
        if ($user->status === 'suspended') {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Your account has been suspended. Please contact the administrator.');
        }

        // Only admin, vendor, or staff can access dashboard
        if (!in_array($user->role, ['admin', 'vendor', 'staff'], true)) {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Access denied. Vendor or Administrator privileges are required.');
        }

        // If a specific permission is required for this route
        if ($permission && !$user->hasPermission($permission)) {
            abort(403, "You do not have permission to access {$permission}. Contact the administrator.");
        }

        return $next($request);
    }
}
