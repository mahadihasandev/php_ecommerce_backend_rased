# Dashboard and API performance

## Changes

- Dashboard cache misses use four SQL queries instead of sixteen: one statement for scalar metrics and three small card queries. The card queries no longer fetch unused brand/category relations or full descriptions.
- Admin catalog, category, brand, banner, order and user lists reuse paginated data. Keys include the URL, sorted filters, current user ID and role. Authentication and Blade rendering still run on every request; HTML, session cookies and CSRF tokens are never cached.
- Catalog reads share `PerformanceCache`: fresh for 60 seconds, usable for up to 300 seconds while Laravel refreshes after the response under a cache lock. This covers existing lists plus product/category/brand details, search and blogs. Cold requests still query the database.
- Successful admin writes rotate cache generations after the complete action. Registrations invalidate only user-dependent caches; checkout invalidates only order/sales-dependent caches. Public registration and checkout routes are rate limited, and validation failures do not invalidate their caches. Old generations expire naturally, and in-flight refreshes cannot populate the new generation. The dashboard's Flush Cache action invalidates all these caches. Existing externally cached catalog responses may remain visible for up to 15 seconds.
- Imports, jobs and direct database changes outside these HTTP routes should call `App\Services\PerformanceCache::invalidate()` after committing their transaction. Otherwise freshness follows the 60/300-second policy.
- Best sellers aggregate order quantities once, exclude cancelled orders and treat missing sales as zero. ID breaks ties consistently, including PostgreSQL's different default NULL ordering. API fields and existing array/paginator shapes remain intact.
- Eleven indexes support date ordering, vendor lists, status filters and relationship lookups. PostgreSQL builds them concurrently and recovers invalid indexes left by interrupted builds; SQLite/MySQL use schema indexes.
- Order lists use item counts rather than loading every item and product. Order details use the actual `products` relationship and JSON shipping address.
- The JavaScript imports only the icons used by Blade. Assets remain precompiled; Nginx caches fingerprinted assets for one year and fallback assets/fonts for one hour. Duplicate/early icon initialization was removed.
- Docker uses Node 22 and a synchronized lockfile with `npm ci` for reproducible builds.

## Validation (2026-09-27)

| Check | Original | Updated |
| --- | ---: | ---: |
| Cold dashboard SQL queries, seeded local app | 16 | 4 |
| Warm dashboard catalog queries | 0 | 0 |
| JavaScript, minified | 472.10 KB | 64.60 KB |
| JavaScript, gzip | 114.18 KB | 23.05 KB |
| Best-seller SQL median, local PostgreSQL | 45.917 ms | 21.177 ms |

The SQL benchmark used 5,000 products, 1,000 orders and 100,000 order items; five `EXPLAIN ANALYZE` runs per query. The original correlated query was also given the new indexes, isolating the benefit of grouped aggregation. These are local measurements, not production latency guarantees.

The full PHP suite passes on SQLite and PostgreSQL (28 tests, 245 assertions per database). Regression coverage includes query budgets, warm reads, stale refreshes, invalidation after product/category updates and checkout, deletion, manual purge, vendor isolation, filtering/pagination, order addresses, scoped invalidation, registration throttling and best-seller ranking. A failed concurrent PostgreSQL index build was reproduced, repaired by the migration, and verified to remain intact on a subsequent rerun. Browser verification covers login, dashboard and product navigation, icon rendering and Alpine initialization.

Live baseline from the unchanged deployment, measured from this workstation: `/api/products` approximately 3.07 seconds initially and 0.33 seconds on repeat; `/api/products/best-sellers` approximately 3.28 seconds. `/api/health` was approximately 0.52 seconds. The difference is consistent with expensive uncached database work but does not isolate database network latency from SQL execution.

## Applying the update

1. Deploy the branch after review; the live service is not changed merely by this patch.
2. Build assets with Node 22 (`npm ci --ignore-scripts && npm run build`) and install the locked PHP dependencies. The Dockerfile does this during build.
3. Run `php artisan migrate --force`. Review deployment logs: the existing entrypoint logs migration failures without stopping the deployment. Verify the new index migration completed.
4. Run `php artisan config:cache`, `php artisan route:cache` and `php artisan view:cache`, or use the existing Docker entrypoint. Restart PHP-FPM through a container deployment because production OPcache disables timestamp validation.
5. Use `APP_ENV=production`, `APP_DEBUG=false` and a local file cache or correctly configured Redis cache. Redis must be shared if the application runs on multiple instances so cache invalidation reaches all of them. Avoid a remote database-backed cache for these frequently accessed values.
6. Check the configured session store separately. Database sessions add database work to every Blade request; moving to a suitable session store needs a deployment decision because existing users may be signed out. This patch does not change the session store.
7. Compare cold, repeated and post-60-second requests on the deployed service, including authenticated dashboard navigation. Verify product/category changes and new orders become visible. No credentials are needed for public catalog checks.

Render's free services may spin down after inactivity; application optimizations cannot remove the platform's cold start. Its documented free-instance behavior and resource limits still apply. See [Render free services](https://render.com/docs/free) and [Laravel cache documentation](https://laravel.com/framework/docs/13.x/cache#stale-while-revalidate).

To reproduce correctness checks locally, provide a local application key and run `php artisan test`. PostgreSQL checks should use a dedicated disposable test database via `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`; tests reset that database. `npm run build` prints the bundle sizes.
