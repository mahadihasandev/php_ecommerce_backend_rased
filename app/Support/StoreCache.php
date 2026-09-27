<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class StoreCache
{
    public const TTL = 60;

    private const VERSION_KEY = 'store_cache_version';

    public static function key(string $key): string
    {
        return 'store:'.Cache::get(self::VERSION_KEY, 'initial').':'.$key;
    }

    public static function invalidate(): void
    {
        // Old entries expire naturally; no cache-wide flush or Redis-only tags.
        Cache::forever(self::VERSION_KEY, (string) Str::uuid());
    }
}
