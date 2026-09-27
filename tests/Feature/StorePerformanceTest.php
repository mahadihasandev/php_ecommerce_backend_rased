<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Middleware\CacheCatalogResponse;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\StoreCache;
use Database\Seeders\EcommerceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EcommerceSeeder::class);
        Cache::flush();
    }

    public function test_public_catalog_cache_preserves_json_and_avoids_database_queries(): void
    {
        $product = Product::firstOrFail();
        $paths = [
            '/api/products', '/api/products?paginate=1&limit=2&page=2',
            '/api/products/'.$product->id, '/api/products/search?q=phone',
            '/api/products/best-sellers', '/api/products/best-sellers?paginate=1&limit=2',
            '/api/products/hot-deals', '/api/categories', '/api/brands',
            '/api/banners', '/api/blogs', '/api/blogs/latest', '/api/blogs/others', '/api/blog-categories',
        ];

        foreach ($paths as $path) {
            $first = $this->getJson($path)->assertOk();
            DB::enableQueryLog();
            DB::flushQueryLog();
            $cached = $this->getJson($path)->assertOk();
            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            $this->assertSame($first->getContent(), $cached->getContent(), $path);
            $this->assertCount(0, $queries, $path);
        }
    }

    public function test_filters_pages_and_response_shapes_have_separate_cache_entries(): void
    {
        $first = $this->getJson('/api/products?limit=1&page=1')->assertOk()->json();
        $second = $this->getJson('/api/products?limit=1&page=2')->assertOk()->json();
        $this->assertNotSame($first[0]['id'], $second[0]['id']);
        $this->assertIsArray($first[0]['slug']);
        $this->assertArrayHasKey('categories', $first[0]);

        $this->getJson('/api/products?limit=1&page=2&paginate=1')
            ->assertOk()->assertJsonPath('current_page', 2)->assertJsonPath('per_page', 1)
            ->assertJsonPath('data.0.id', $second[0]['id']);
        $this->getJson('/api/products?category=does-not-exist')->assertExactJson([]);
        $best = $this->getJson('/api/products/best-sellers?limit=1')->assertOk()->json();
        $this->assertIsInt($best[0]['sales_count']);
        $this->assertArrayHasKey('name', $best[0]);
    }

    public function test_catalog_cache_expires_for_changes_outside_http_requests(): void
    {
        $product = Product::firstOrFail();
        $this->getJson('/api/products/'.$product->id)->assertOk();
        $product->update(['name' => 'Updated by an import']);
        $this->travel(StoreCache::TTL + 1)->seconds();
        $this->getJson('/api/products/'.$product->id)->assertJsonPath('name', 'Updated by an import');
    }

    public function test_admin_write_invalidates_catalog_including_category_pivots(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $product = Product::firstOrFail();
        $category = Category::create(['title' => 'New grouping', 'slug' => 'new-grouping']);
        $this->getJson('/api/products/'.$product->id)->assertOk();
        $this->getJson('/api/products?category=new-grouping')->assertExactJson([]);

        $this->actingAs($admin)->put('/admin/products/'.$product->id, [
            'name' => 'Updated product', 'price' => 250, 'stock' => 12,
            'category_ids' => [$category->id],
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/products');

        $this->getJson('/api/products/'.$product->id)->assertJsonPath('name', 'Updated product')
            ->assertJsonPath('categories.0.id', $category->id);
        $this->getJson('/api/products?category=new-grouping')->assertJsonPath('0.id', $product->id);
    }

    public function test_order_write_invalidates_best_seller_totals(): void
    {
        $product = Product::firstOrFail();
        $this->getJson('/api/products/best-sellers')->assertOk();
        $this->postJson('/api/orders', [
            'orderNumber' => 'performance-order', 'customerName' => 'Test customer',
            'email' => 'customer@example.test', 'totalPrice' => 100,
            'products' => [['product_id' => $product->id, 'quantity' => 1000, 'price' => 0.1]],
        ])->assertCreated();
        $result = $this->getJson('/api/products/best-sellers')->assertOk();
        $result->assertJsonPath('0.id', $product->id);
        $this->assertGreaterThanOrEqual(1000, $result->json('0.sales_count'));
    }

    public function test_errors_are_not_cached(): void
    {
        $this->getJson('/api/products/new-product')->assertNotFound();
        Product::create(['name' => 'New product', 'slug' => 'new-product', 'price' => 10, 'stock' => 1]);
        $this->getJson('/api/products/new-product')->assertOk()->assertJsonPath('name', 'New product');
    }

    public function test_private_routes_remain_outside_catalog_cache(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $this->actingAs($first, 'sanctum')->getJson('/api/user')->assertJsonPath('user.id', $first->id);
        $this->actingAs($second, 'sanctum')->getJson('/api/user')->assertJsonPath('user.id', $second->id);
        $this->assertFalse(str_contains($this->getJson('/api/user')->headers->get('Cache-Control', ''), 'public'));

        foreach (['api/user', 'api/logout', 'api/orders', 'api/addresses', 'admin'] as $path) {
            $route = app('router')->getRoutes()->match(Request::create('/'.$path, $path === 'api/logout' ? 'POST' : 'GET'));
            $this->assertNotContains(CacheCatalogResponse::class, $route->gatherMiddleware());
        }
    }

    public function test_dashboard_needs_four_cold_queries_and_preserves_vendor_scope(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $view = app(DashboardController::class)->index();
        $view->render();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertCount(4, $queries);
        $this->assertEquals(Product::count(), $view->getData()['totalProducts']);
        $this->assertNotNull($view->getData()['bestSellers']->first()->stock);

        $vendor = User::factory()->create(['role' => 'vendor', 'status' => 'active']);
        $owned = Product::create(['name' => 'Vendor product', 'slug' => 'vendor-product', 'price' => 10, 'stock' => 3, 'user_id' => $vendor->id]);
        $this->actingAs($vendor);
        $data = app(DashboardController::class)->index()->getData();
        $this->assertEquals(1, $data['totalProducts']);
        $this->assertEquals(1, $data['lowStockCount']);
        $this->assertSame([$owned->id], $data['recentProducts']->pluck('id')->all());
        $this->assertSame([$owned->id], $data['bestSellers']->pluck('id')->all());
    }
}
