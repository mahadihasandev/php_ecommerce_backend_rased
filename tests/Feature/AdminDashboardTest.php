<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@ecommerce.com'],
            [
                'name' => 'Store Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'permissions' => array_keys(User::PERMISSIONS),
            ]
        );

        $this->vendor = User::firstOrCreate(
            ['email' => 'vendor@store.com'],
            [
                'name' => 'Apex Vendor',
                'password' => Hash::make('password123'),
                'role' => 'vendor',
                'status' => 'active',
                'store_name' => 'Apex Electronics',
                'permissions' => ['manage_products', 'manage_orders'],
            ]
        );
    }

    public function test_unauthenticated_user_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Marketplace & Store Overview');
    }

    public function test_admin_can_view_products_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/products');
        $response->assertStatus(200);
        $response->assertSee('Catalog Products');
    }

    public function test_admin_can_create_product_with_image_upload(): void
    {
        $category = Category::firstOrCreate(['slug' => 'test-cat'], ['title' => 'Test Cat', 'name' => 'Test Cat']);
        $brand = Brand::firstOrCreate(['slug' => 'test-brand'], ['title' => 'Test Brand', 'name' => 'Test Brand']);

        $image = UploadedFile::fake()->image('test_product.jpg', 600, 600);

        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Automated Test Product',
            'slug' => 'automated-test-product',
            'price' => 199.99,
            'discount' => 149.99,
            'stock' => 50,
            'status' => 'new',
            'variant' => 'gadget',
            'isFeatured' => 1,
            'brand_id' => $brand->id,
            'category_ids' => [$category->id],
            'images' => [$image],
            'keyfeature' => ['Feature Alpha', 'Feature Beta'],
            'description' => 'Test product description for verification.',
        ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'name' => 'Automated Test Product',
            'price' => 199.99,
        ]);
    }

    public function test_admin_can_create_banner_with_image_upload(): void
    {
        $image = UploadedFile::fake()->image('banner_hero.jpg', 1200, 500);

        $response = $this->actingAs($this->admin)->post('/admin/banners', [
            'title' => 'Flash Holiday Sale',
            'subtitle' => 'Special Deals',
            'badge' => 'LIMITED TIME',
            'discountAmount' => 40,
            'link' => '/shop',
            'image' => $image,
            'description' => 'Huge savings this holiday season.',
        ]);

        $response->assertRedirect('/admin/banners');
        $this->assertDatabaseHas('banners', [
            'title' => 'Flash Holiday Sale',
            'discountAmount' => 40,
        ]);
    }

    public function test_admin_can_manage_user_permissions(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Vendor Directory');

        // Update vendor permissions
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->vendor->id}", [
            'name' => 'Apex Vendor Updated',
            'email' => 'vendor@store.com',
            'role' => 'vendor',
            'status' => 'active',
            'store_name' => 'Apex Tech Superstore',
            'permissions' => ['manage_products', 'manage_banners', 'manage_orders'],
        ]);

        $response->assertRedirect('/admin/users');
        
        $this->vendor->refresh();
        $this->assertEquals('Apex Tech Superstore', $this->vendor->store_name);
        $this->assertTrue($this->vendor->hasPermission('manage_banners'));
    }

    public function test_vendor_without_banner_permission_is_forbidden(): void
    {
        $restrictedVendor = User::create([
            'name' => 'Restricted Merchant',
            'email' => 'restricted@vendor.com',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'status' => 'active',
            'permissions' => ['manage_products'], // Does NOT have manage_banners
        ]);

        // Attempting to access banners creation
        $response = $this->actingAs($restrictedVendor)->get('/admin/banners/create');
        // Layout hides banner link, and FormRequest or middleware checks authorization
        $image = UploadedFile::fake()->image('banner.jpg');
        $postResponse = $this->actingAs($restrictedVendor)->post('/admin/banners', [
            'title' => 'Unauthorized Banner',
            'image' => $image,
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_product_keyfeature_accessor_handles_arrays_and_strings(): void
    {
        // 1. Array input
        $product = Product::create([
            'user_id' => $this->admin->id,
            'name' => 'Noise Cancelling Headphones',
            'slug' => 'noise-cancelling-headphones',
            'price' => 299.99,
            'stock' => 10,
            'keyfeature' => ['Active Noise Cancellation', '30h Battery Life', 'Multipoint Bluetooth'],
        ]);

        $product->refresh();
        $this->assertIsArray($product->keyfeature);
        $this->assertCount(3, $product->keyfeature);
        $this->assertEquals('Active Noise Cancellation', $product->keyfeature[0]);

        // 2. Legacy sentence string input should be parsed into distinct sentences, not 125 individual characters
        $legacyText = 'Industry-leading noise cancellation with two processors and 8 microphones. Up to 30-hour battery life with quick charging.';
        $product->keyfeature = $legacyText;
        $product->save();
        $product->refresh();

        $this->assertIsArray($product->keyfeature);
        $this->assertCount(2, $product->keyfeature);
        $this->assertEquals('Industry-leading noise cancellation with two processors and 8 microphones.', $product->keyfeature[0]);
        $this->assertEquals('Up to 30-hour battery life with quick charging.', $product->keyfeature[1]);

        // 3. Edit page renders valid array for Alpine.js
        $response = $this->actingAs($this->admin)->get("/admin/products/{$product->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Industry-leading noise cancellation with two processors and 8 microphones.');
    }
}
