<?php

namespace App\Http\Middleware;

use App\Support\StoreCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvalidateStoreCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Run after the controller completes, including relationship/pivot writes.
        if (! $request->isMethodSafe()
            && $response->getStatusCode() < 400
            && $request->is('admin/*', 'api/orders', 'api/register')) {
            StoreCache::invalidate();
        }

        return $response;
    }
}
