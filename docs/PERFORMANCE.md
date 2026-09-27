# Dashboard and storefront API performance

## Changes

- Dashboard counters use one SQL round trip instead of nine. Recent-product and best-seller cards load only displayed columns and no unused brand/category relations. The cold dashboard dataset now needs four queries instead of sixteen; the existing 60-second per-user data cache remains. The cache key includes both user ID and role. HTML, CSRF tokens and session messages are not cached.
- Public catalog GET routes share a cache of the serialized JSON response for 60 seconds. This covers product lists, detail, search, deals, best sellers, categories, brands, banners and blogs. A warm hit avoids both database queries and Eloquent serialization. Query parameters and request origin/path are part of the key, keeping filters, pages, response formats and pagination URLs separate.
- Successful admin mutations, API order creation and API customer registration invalidate the catalog/dashboard cache generation after the controller finishes, including category pivot changes. The dashboard's Flush Cache button uses the same invalidation. Auth/profile, order and address responses are never added to the public catalog cache.
- Browsers may cache catalog responses for 15 seconds; shared caches for 30 seconds with a further 30-second stale-while-revalidate window. Origin invalidation does not purge a browser/CDN's existing response. Direct database edits/imports outside HTTP requests become visible after the 60-second origin TTL, or immediately at origin after `App\Support\StoreCache::invalidate()`.
- New indexes support product/category relations, best-seller order-item lookups, vendor product ordering and recent product/order/blog ordering. Existing API response fields and pagination formats are retained.
- The admin JavaScript imports only the 44 icons used in Blade templates instead of the entire Lucide catalog. Keep `resources/js/icons.js` aligned when introducing icons. Fingerprinted `/build/assets/` files receive immutable one-year caching; HTML and non-fingerprinted assets do not.

## Local verification

Measured with the repository seed data, PHP 8.5.7, SQLite, and file cache:

| Measurement | Before | After |
| --- | --- | --- |
| Cold dashboard data/render queries | 16 | 4 |
| Warm dashboard data/render queries | 0 | 0 |
| Vite admin JavaScript | 472.10 KB | 64.60 KB |
| Gzipped admin JavaScript | 114.18 KB | 23.05 KB |

`StorePerformanceTest` checks warm catalog reads perform zero queries, unchanged JSON, filters/pagination, TTL expiry, category pivot invalidation, order-driven best-seller invalidation, uncached errors, private route exclusion, and vendor dashboard scoping. Existing feature tests continue to cover login and dashboard CRUD.

Validation commands:

```sh
php artisan test
vendor/bin/pint --dirty --test
npm run build
php artisan route:cache
php artisan view:cache
```

The new migration was also migrated, rolled back, and migrated again on a disposable local SQLite database. Production PostgreSQL index creation and live latency still require verification after deployment. Local query counts and asset sizes are not promises of production response times.

## Deployment

Build/deploy the reviewed branch through the existing pipeline. Its entrypoint already caches configuration, routes and views and runs migrations when `AUTO_MIGRATE=true`. If automatic migrations are disabled, run `php artisan migrate --force` during deployment. Index creation can take longer on large production tables, so schedule accordingly.

Use an available low-latency cache store (`file` or the configured Redis service). Check the actual deployment's cache/session/database configuration and application logs if warm endpoints remain slow. These changes do not change the Render plan or eliminate platform cold starts after idle shutdown.

The existing npm lockfile is not accepted by this environment's `npm ci` (missing React peer entry). Validation used `npm install --ignore-scripts --package-lock=false` without changing dependencies or the lockfile, followed by `npm run build`, matching the project's existing install-based Docker build approach.
