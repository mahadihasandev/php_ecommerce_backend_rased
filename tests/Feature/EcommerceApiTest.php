<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\EcommerceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EcommerceSeeder::class);
    }

    public function test_can_register_new_user(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['status', 'message', 'user', 'token']);
    }

    public function test_can_login_existing_user(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'user', 'token']);
    }

    public function test_can_fetch_authenticated_user_profile(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'test@example.com');
    }

    public function test_can_list_products_and_categories(): void
    {
        $productsRes = $this->getJson('/api/products');
        $productsRes->assertStatus(200);

        $categoriesRes = $this->getJson('/api/categories');
        $categoriesRes->assertStatus(200);

        $brandsRes = $this->getJson('/api/brands');
        $brandsRes->assertStatus(200);

        $bannersRes = $this->getJson('/api/banners');
        $bannersRes->assertStatus(200);
    }
}
