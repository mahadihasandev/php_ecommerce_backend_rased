# Laravel E-Commerce Backend & Multi-Vendor Admin Portal

## Project Overview
- **Framework**: Laravel 11 / 12 on PHP 8.4 Alpine + Nginx Container
- **Live Render URL**: https://php-ecommerce-backend-rased.onrender.com
- **Admin Dashboard**: https://php-ecommerce-backend-rased.onrender.com/admin
- **Frontend Storefront**: https://e-commerce-rased.onrender.com

---

## Keep-Alive & Inactivity Prevention (Render Free Tier)
Render free tier containers spin down after 15 minutes of inactivity. To keep both Frontend and Backend continuously awake 24/7 without rebuilding:

1. **In-Container Auto-Pinger (`docker/entrypoint.sh`)**:
   - A background subshell loops every 13 minutes (780 seconds), executing `curl -s` to both backend (`/health`) and frontend (`/api/health`).
2. **GitHub Actions 24/7 Cloud Cron (`.github/workflows/keep-alive.yml`)**:
   - Runs every 13 minutes (`cron: '*/13 * * * *'`) in GitHub Cloud, ensuring zero downtime even if no user visits.
3. **Dedicated Public Health Routes**:
   - `GET /health` (Web route, bypasses auth)
   - `GET /api/health` (API route, instant JSON status)
4. **Standalone Local Pinger**:
   - Run `node keep_alive_render.js` from the workspace root.

---

## Caching & Performance Architecture
- **Default Cache Store**: Set to `file` in local development (`CACHE_STORE=file`) for sub-2ms response times without local Redis dependencies.
- **Production Redis**: `CACHE_STORE=redis` on Render utilizes the in-container Redis server daemon (`127.0.0.1:6379`).
- **Connection Fast-Fail**: `config/database.php` configures Predis with `timeout: 0.2s` and `max_retries: 1` so missing Redis daemons never hang.
- **Serialization Safety**: `config/cache.php` sets `'serializable_classes' => true` to safely serialize and deserialize Eloquent models without `__PHP_Incomplete_Class` errors.
- **Self-Healing Guard**: `DashboardController` automatically verifies cached structures and purges stale keys if corruption is detected.

---

## Blade Dashboard Instant Navigation & Skeletons
- **Instant Client Router (`window.AdminNav` in `app.blade.php`)**:
  - Intercepts internal admin links (`/admin/*`).
  - Instantly activates `#admin-top-progress` glowing bar (0ms delay).
  - Swaps content via AJAX while displaying matching shimmer skeleton views (Dashboard, Table, or Form) from `resources/views/admin/components/skeleton.blade.php`.
  - Re-initializes Lucide icons and Alpine components dynamically without tearing down the browser DOM or re-executing Tailwind CDN.

---

## Database Migrations & Production Resilience
- **PostgreSQL / Supabase Compatibility**:
  - `database/migrations/2026_09_25_000001_add_performance_indexes.php`: Idempotent index creation wrapped in try-catch to avoid `42P07` duplicate relation errors.
  - `database/migrations/2026_09_26_000001_fix_order_items_product_id_type.php`: Safely alters `product_id` to bigint using PostgreSQL `USING (NULLIF(product_id, '')::bigint)`.
- **Permissions**: `docker/entrypoint.sh` creates `storage/framework/cache/data` and guarantees `www-data` ownership after all bootstrap and migration commands.
