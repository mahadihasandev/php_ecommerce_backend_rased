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
        Schema::table('products', function (Blueprint $table) {
            $table->index('status');
            $table->index('variant');
            $table->index('isFeatured');
            $table->index('price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('orderDate');
        });
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
