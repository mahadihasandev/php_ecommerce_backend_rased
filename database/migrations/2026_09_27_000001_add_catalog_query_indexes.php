<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEXES = [
        'order_items' => ['order_items_product_order_index' => ['product_id', 'order_id']],
        'category_product' => [
            'category_product_product_category_index' => ['product_id', 'category_id'],
            'category_product_category_product_index' => ['category_id', 'product_id'],
        ],
        'products' => [
            'products_created_at_index' => ['created_at'],
            'products_user_created_index' => ['user_id', 'created_at'],
        ],
        'orders' => ['orders_created_at_index' => ['created_at']],
        'blogs' => ['blogs_published_at_index' => ['publishedAt']],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (! Schema::hasIndex($tableName, $name)) {
                    Schema::table($tableName, fn (Blueprint $table) => $table->index($columns, $name));
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($tableName, $name)) {
                    Schema::table($tableName, fn (Blueprint $table) => $table->dropIndex($name));
                }
            }
        }
    }
};
