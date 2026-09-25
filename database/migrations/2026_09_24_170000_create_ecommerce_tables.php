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
        // Add extra fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar')->nullable()->after('phone');
        });

        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('_id')->nullable()->index();
            $table->string('title');
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->integer('productCount')->default(0);
            $table->timestamps();
        });

        // Brands
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('_id')->nullable()->index();
            $table->string('title');
            $table->string('name')->nullable();
            $table->string('brandName')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->timestamps();
        });

        // Banners
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('_id')->nullable()->index();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->string('badge')->nullable();
            $table->integer('discountAmount')->nullable();
            $table->string('link')->nullable();
            $table->timestamps();
        });

        // Authors
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('image')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        // Blog Categories
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Blogs
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('mainImage')->nullable();
            $table->timestamp('publishedAt')->nullable();
            $table->json('body')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->timestamps();
        });

        // Blog - Blog Category Pivot
        Schema::create('blog_blog_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->foreignId('blog_category_id')->constrained('blog_categories')->cascadeOnDelete();
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('status')->nullable(); // 'new', 'hot', 'sale'
            $table->string('variant')->nullable(); // 'gadget', 'appliances', 'smartphones', etc.
            $table->boolean('isFeatured')->default(false);
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->json('images')->nullable();
            $table->json('keyfeature')->nullable();
            $table->json('description')->nullable();
            $table->timestamps();
        });

        // Product - Category Pivot
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
        });

        // Addresses
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('_id')->nullable()->index();
            $table->string('user_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('District')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('default')->default(false);
            $table->timestamps();
        });

        // Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('_id')->nullable()->index();
            $table->string('orderNumber')->unique();
            $table->string('user_id')->nullable()->index();
            $table->string('customerName');
            $table->string('email');
            $table->decimal('totalPrice', 10, 2);
            $table->decimal('amountDiscount', 10, 2)->default(0);
            $table->string('currency')->default('BDT');
            $table->string('status')->default('pending');
            $table->timestamp('orderDate')->useCurrent();
            $table->string('stripeCheckoutSessionId')->nullable();
            $table->string('stripeCustomerId')->nullable();
            $table->string('stripePaymentIntentId')->nullable();
            $table->json('invoice')->nullable();
            $table->json('address')->nullable();
            $table->timestamps();
        });

        // Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('product_id')->nullable();
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('blog_blog_category');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'avatar']);
        });
    }
};
