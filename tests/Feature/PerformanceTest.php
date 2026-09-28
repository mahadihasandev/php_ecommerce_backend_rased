<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PerformanceCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $slug, array $attributes = []): Product
    {
        return Product::create($attributes + ['name' => $slug, 'slug' => $slug, 'price' => 100, 'stock' => 8]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    private function order(string $number, string $status = 'paid'): Order
    {
        return Order::create([
            'orderNumber' => $number, 'customerName' => 'Test Buyer', 'email' => 'buyer@example.test',
            'totalPrice' => 100, 'status' => $status,
            'address' => ['name' => 'Test Buyer', 'address' => '123 Test Road', 'city' => 'Dhaka', 'zip' => '1200', 'phone' => '123456'],
        ]);
    }

    public function test_dashboard_cold_query_budget_and_totals(): void
    {
        $this->actingAs($this->admin());
        $this->product('low-stock', ['stock' => 2]);
        $this->product('in-stock');
        $this->order('paid');
        $this->order('cancelled', 'cancelled');
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->get('/admin')->assertOk()
            ->assertViewHas('totalProducts', 2)->assertViewHas('lowStockCount', 1)
            ->assertViewHas('totalOrders', 2)->assertViewHas('totalRevenue', 100.0)
            ->assertViewHas('totalUsers', 1);
        $this->assertCount(4, DB::getQueryLog(), 'One metrics query and three card queries');
        DB::disableQueryLog();
    }

    public function test_best_sellers_rank_sales_exclude_cancelled_orders_and_keep_pagination_shape(): void
    {
        $unsold = $this->product('unsold');
        $second = $this->product('second');
        $first = $this->product('first');
        $order = $this->order('paid-order');
        $order->products()->createMany([
            ['product_id' => $second->id, 'quantity' => 2, 'price' => 100],
            ['product_id' => $first->id, 'quantity' => 5, 'price' => 100],
        ]);
        $this->order('cancelled-order', 'cancelled')->products()->create(['product_id' => $unsold->id, 'quantity' => 100, 'price' => 100]);

        $this->getJson('/api/products/best-sellers')->assertOk()
            ->assertJsonPath('0.id', $first->id)->assertJsonPath('0.sales_count', 5)
            ->assertJsonPath('1.id', $second->id)->assertJsonPath('2.id', $unsold->id)->assertJsonPath('2.sales_count', 0);
        $this->getJson('/api/products/best-sellers?paginate=1&limit=1&page=2')->assertOk()
            ->assertJsonPath('total', 3)->assertJsonPath('current_page', 2)->assertJsonPath('data.0.id', $second->id);
    }

    public function test_unsold_products_have_deterministic_order(): void
    {
        $first = $this->product('one');
        $second = $this->product('two');
        $this->getJson('/api/products/best-sellers')->assertOk()
            ->assertJsonPath('0.id', $first->id)->assertJsonPath('1.id', $second->id);
    }

    public function test_repeated_public_reads_do_not_query_database_and_preserve_details(): void
    {
        $brand = Brand::create(['title' => 'Brand', 'slug' => 'brand']);
        $category = Category::create(['title' => 'Category', 'slug' => 'category']);
        $product = $this->product('searchable', ['brand_id' => $brand->id, 'description' => ['full detail']]);
        $product->categories()->attach($category);

        $urls = ['/api/products', '/api/products/search?q=searchable', '/api/products/searchable', '/api/products/hot-deals',
            '/api/products/best-sellers', '/api/categories', '/api/categories/category', '/api/brands', '/api/brands/brand',
            '/api/banners', '/api/blogs', '/api/blogs/latest', '/api/blogs/others', '/api/blog-categories'];
        foreach ($urls as $url) {
            $cold = $this->getJson($url)->assertOk()->json();
            DB::enableQueryLog();
            DB::flushQueryLog();
            $warm = $this->getJson($url)->assertOk()->json();
            $this->assertSame([], DB::getQueryLog(), $url.' should use the cache');
            $this->assertSame($cold, $warm);
            DB::disableQueryLog();
        }
        $this->getJson('/api/products/searchable')->assertJsonPath('slug.current', 'searchable')
            ->assertJsonPath('description.0', 'full detail')->assertJsonPath('brand.id', $brand->id)
            ->assertJsonPath('categories.0.id', $category->id);
        $this->getJson('/api/products/missing')->assertNotFound();
    }

    public function test_product_filter_key_ignores_query_order_and_tracking_parameters(): void
    {
        $this->product('phone', ['variant' => 'Phone']);
        $this->getJson('/api/products?variant=phone&limit=2')->assertOk()->assertJsonCount(1);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/api/products?limit=2&utm_source=test&variant=phone')->assertOk()->assertJsonCount(1);
        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_admin_product_update_invalidates_all_variants_after_category_sync(): void
    {
        $admin = $this->admin();
        $product = $this->product('old-product', ['user_id' => $admin->id]);
        $category = Category::create(['title' => 'New category', 'slug' => 'new-category']);
        $this->getJson('/api/products')->assertJsonPath('0.price', 100);
        $this->getJson('/api/products/old-product')->assertJsonCount(0, 'categories');
        $this->actingAs($admin)->get('/admin/products')->assertOk()->assertSee('old-product');
        $this->put('/admin/products/'.$product->id, [
            'name' => 'Updated product', 'slug' => 'old-product', 'price' => 200, 'stock' => 3,
            'category_ids' => [$category->id],
        ])->assertRedirect('/admin/products');
        $this->getJson('/api/products')->assertJsonPath('0.price', 200);
        $this->getJson('/api/products/old-product')->assertJsonPath('categories.0.id', $category->id);
        $this->get('/admin/products')->assertOk()->assertSee('Updated product');
        $this->get('/admin')->assertOk()->assertViewHas('lowStockCount', 1);
    }

    public function test_delete_invalidates_product_details_and_purge_clears_all_catalog_keys(): void
    {
        $this->actingAs($this->admin());
        $product = $this->product('delete-me');
        $this->getJson('/api/products/delete-me')->assertOk();
        $this->delete('/admin/products/'.$product->id)->assertRedirect('/admin/products');
        $this->getJson('/api/products/delete-me')->assertNotFound();

        $this->getJson('/api/brands')->assertJsonCount(0);
        Brand::create(['title' => 'Imported brand', 'slug' => 'imported-brand']);
        $this->post('/admin/purge-cache')->assertRedirect();
        $this->getJson('/api/brands')->assertJsonCount(1);
    }

    public function test_cached_admin_filters_and_pages_remain_distinct(): void
    {
        $this->actingAs($this->admin());
        for ($i = 0; $i < 13; $i++) {
            $this->product('catalog-'.$i, ['stock' => $i === 0 ? 0 : 8]);
        }
        $this->get('/admin/products?stock_status=out')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 1);
        $this->get('/admin/products?page=2')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->currentPage() === 2 && $products->count() === 1 && $products->total() === 13);
        $this->get('/admin/products?page=1')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->count() === 12);
    }

    public function test_dashboard_and_product_list_cache_stays_scoped_to_each_vendor(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor', 'status' => 'active', 'permissions' => ['manage_products']]);
        $other = User::factory()->create(['role' => 'vendor', 'status' => 'active', 'permissions' => ['manage_products']]);
        $this->product('vendor-one-only', ['user_id' => $vendor->id]);
        $this->product('vendor-two-only', ['user_id' => $other->id]);
        foreach ([$vendor, $other, $vendor] as $user) {
            $own = $user->id === $vendor->id ? 'vendor-one-only' : 'vendor-two-only';
            $hidden = $user->id === $vendor->id ? 'vendor-two-only' : 'vendor-one-only';
            $this->actingAs($user)->get('/admin')->assertOk()->assertViewHas('totalProducts', 1)->assertSee($own)->assertDontSee($hidden);
            $this->get('/admin/products')->assertOk()->assertSee($own)->assertDontSee($hidden);
        }
    }

    public function test_admin_lists_reuse_data_but_render_fresh_authenticated_pages(): void
    {
        $this->actingAs($this->admin());
        $this->product('test-product');
        $order = $this->order('test-order');
        foreach (['/admin', '/admin/products', '/admin/categories', '/admin/brands', '/admin/banners', '/admin/orders', '/admin/users'] as $url) {
            $this->get($url)->assertOk();
            DB::enableQueryLog();
            DB::flushQueryLog();
            $this->get($url)->assertOk()->assertSee('csrf-token');
            $this->assertSame([], DB::getQueryLog(), $url.' should not reload catalog data');
            DB::disableQueryLog();
        }
        $this->get('/admin/orders/'.$order->id)->assertOk()->assertSee('123 Test Road')->assertSee('1200');
    }

    public function test_checkout_and_status_change_invalidate_sales_and_order_counts(): void
    {
        $product = $this->product('ordered');
        $this->getJson('/api/products/best-sellers')->assertJsonPath('0.sales_count', 0);
        $response = $this->postJson('/api/orders', [
            'orderNumber' => 'checkout', 'customerName' => 'Buyer', 'email' => 'buyer@example.test',
            'totalPrice' => 200, 'products' => [['productId' => $product->id, 'quantity' => 2, 'price' => 100]],
        ])->assertCreated()->assertJsonCount(1, 'products');
        $this->getJson('/api/products/best-sellers')->assertJsonPath('0.sales_count', 2);
        $this->actingAs($this->admin())->get('/admin/orders')->assertOk()->assertSee('1 items');
        $this->patch('/admin/orders/'.$response->json('id').'/status', ['status' => 'cancelled'])->assertRedirect();
        $this->getJson('/api/products/best-sellers')->assertJsonPath('0.sales_count', 0);
        $this->get('/admin/orders')->assertViewHas('statusCounts', fn ($counts) => $counts['cancelled'] === 1);
    }

    public function test_public_writes_do_not_evict_unrelated_catalog_reads(): void
    {
        $this->product('warm-catalog');
        $this->getJson('/api/products')->assertOk();
        $this->postJson('/api/orders', [
            'orderNumber' => 'scoped-checkout', 'customerName' => 'Buyer', 'email' => 'buyer@example.test', 'totalPrice' => 100,
        ])->assertCreated();
        $this->postJson('/api/register', [
            'name' => 'New Customer', 'email' => 'new@example.test', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/api/products')->assertOk()->assertJsonPath('0.name', 'warm-catalog');
        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_invalid_registration_does_not_invalidate_and_registration_is_throttled(): void
    {
        PerformanceCache::remember('user-cache', fn () => 'original', ['users']);
        $this->post('/admin/register', [])->assertSessionHasErrors();
        $this->assertSame('original', PerformanceCache::remember('user-cache', fn () => 'replaced', ['users']));
        Cache::flush();
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/register', [])->assertUnprocessable();
        }
        $this->postJson('/api/register', [])->assertTooManyRequests();
    }

    public function test_stale_cache_returns_before_refresh_and_invalidation_discards_old_generation(): void
    {
        Cache::flush();
        $calls = 0;
        $compute = function () use (&$calls) {
            return ++$calls;
        };
        $this->assertSame(1, PerformanceCache::remember('test', $compute));
        $this->travel(61)->seconds();
        $this->assertSame(1, PerformanceCache::remember('test', $compute));
        $this->assertSame(1, $calls, 'Stale reads must not block on recomputation');
        app(DeferredCallbackCollection::class)->invoke();
        $this->assertSame(2, PerformanceCache::remember('test', $compute));
        $this->travel(61)->seconds();
        $this->assertSame(2, PerformanceCache::remember('test', $compute));
        PerformanceCache::invalidate();
        $this->assertSame(3, PerformanceCache::remember('test', $compute));
        app(DeferredCallbackCollection::class)->invoke();
        $this->assertSame(3, PerformanceCache::remember('test', $compute), 'Old refresh must not overwrite a new generation');
        $this->travelBack();
    }
}
