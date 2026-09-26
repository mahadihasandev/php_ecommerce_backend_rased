<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->index('status');
                $table->index('variant');
                $table->index('isFeatured');
                $table->index('price');
            });
        } catch (\Throwable $e) {
            // Indexes may already exist on production DB
        }

        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('status');
                $table->index('orderDate');
            });
        } catch (\Throwable $e) {
            // Indexes may already exist on production DB
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['orderDate']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['variant']);
            $table->dropIndex(['isFeatured']);
            $table->dropIndex(['price']);
        });
    }
};
