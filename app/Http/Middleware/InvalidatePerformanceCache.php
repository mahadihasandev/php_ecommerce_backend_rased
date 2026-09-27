<?php

namespace App\Http\Middleware;

use App\Services\PerformanceCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvalidatePerformanceCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Run after the complete controller action (including category sync and order
        // transactions), so cache refreshes cannot capture a partially written record.
        if (! $request->isMethodSafe() && $response->getStatusCode() < 400) {
            PerformanceCache::invalidate();
        }

        return $response;
    }
}
