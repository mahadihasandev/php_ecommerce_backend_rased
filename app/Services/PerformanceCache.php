<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PerformanceCache
{
    private const GENERATION_KEY = 'performance:generation:v2';

    public static function remember(string $key, callable $callback, array $scopes = ['catalog']): mixed
    {
        sort($scopes);
        $generation = implode(':', array_map(
            fn ($scope) => Cache::rememberForever(self::GENERATION_KEY.':'.$scope, fn () => (string) Str::uuid()),
            $scopes
        ));

        // Serve recent data immediately while a single worker refreshes after the response.
        // Old generations expire naturally, so file and Redis stores both work without tags.
        return Cache::flexible("performance:{$generation}:{$key}", [60, 300], $callback, ['seconds' => 30]);
    }

    public static function requestKey(string $prefix, Request $request): string
    {
        $query = $request->query();
        ksort($query);

        return $prefix.hash('sha256', json_encode([
            $request->url(), $query, $request->user()?->id, $request->user()?->role,
        ]));
    }

    public static function invalidate(array $scopes = ['catalog', 'orders', 'users']): void
    {
        foreach ($scopes as $scope) {
            Cache::forever(self::GENERATION_KEY.':'.$scope, (string) Str::uuid());
        }
    }
}
