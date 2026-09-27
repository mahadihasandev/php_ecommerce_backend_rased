<?php

namespace App\Http\Middleware;

use App\Support\StoreCache;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheCatalogResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $query = $request->query();
        ksort($query);
        // Include origin/path: paginator links must belong to this request host.
        $key = StoreCache::key('catalog:'.hash('sha256', $request->url().'|'.json_encode($query)));
        $content = Cache::get($key);

        if (is_string($content)) {
            $response = new Response($content, 200, ['Content-Type' => 'application/json']);
        } else {
            $response = $next($request);

            // Never cache errors, redirects, or a response that sets cookies.
            if (! $response instanceof JsonResponse || $response->getStatusCode() !== 200 || $response->headers->getCookies()) {
                return $response;
            }

            Cache::put($key, $response->getContent(), StoreCache::TTL);
        }

        // Keep edge/browser caching short; authenticated and private routes never use this middleware.
        $response->headers->set('Cache-Control', 'public, max-age=15, s-maxage=30, stale-while-revalidate=30');

        return $response;
    }
}
