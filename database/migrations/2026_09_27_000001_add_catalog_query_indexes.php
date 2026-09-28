<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // PostgreSQL can build these indexes without blocking catalog writes.
    public $withinTransaction = false;

    private function indexes(): array
    {
        return [
            ['products', ['created_at'], 'perf_products_created'],
            ['products', ['user_id', 'created_at'], 'perf_products_vendor_created'],
            ['products', ['brand_id'], 'perf_products_brand'],
            ['orders', ['created_at'], 'perf_orders_created'],
            ['orders', ['status', 'created_at'], 'perf_orders_status_created'],
            ['order_items', ['product_id', 'order_id'], 'perf_items_product_order'],
            ['order_items', ['order_id'], 'perf_items_order'],
            ['category_product', ['product_id', 'category_id'], 'perf_category_product_product'],
            ['category_product', ['category_id', 'product_id'], 'perf_category_product_category'],
            ['users', ['role', 'created_at'], 'perf_users_role_created'],
            ['blogs', ['publishedAt'], 'perf_blogs_published'],
        ];
    }

    public function up(): void
    {
        foreach ($this->indexes() as [$table, $columns, $name]) {
            if (DB::getDriverName() === 'pgsql') {
                $grammar = DB::connection()->getQueryGrammar();
                // A cancelled concurrent build can leave an unusable same-named index.
                // Scope the lookup to the target table so another schema is untouched.
                $invalid = DB::selectOne(
                    'SELECT i.indexrelid FROM pg_index i JOIN pg_class c ON c.oid = i.indexrelid WHERE i.indrelid = to_regclass(?) AND c.relname = ? AND NOT i.indisvalid',
                    [$table, $name]
                );
                if ($invalid) {
                    DB::statement('DROP INDEX CONCURRENTLY '.$grammar->wrap($name));
                }
                $wrappedColumns = implode(', ', array_map($grammar->wrap(...), $columns));
                DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS '.$grammar->wrap($name)
                    .' ON '.$grammar->wrapTable($table).' ('.$wrappedColumns.')');
            } elseif (! Schema::hasIndex($table, $name)) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes()) as [$table, $columns, $name]) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('DROP INDEX CONCURRENTLY IF EXISTS '.DB::connection()->getQueryGrammar()->wrap($name));
            } elseif (Schema::hasIndex($table, $name)) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
            }
        }
    }
};
