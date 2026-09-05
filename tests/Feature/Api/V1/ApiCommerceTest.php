<?php

namespace Tests\Feature\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiCommerceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_login_and_use_cart_then_order(): void
    {
        $product = Product::factory()->inStock(6)->create([
            'name' => 'Câble USB-C API',
            'selling_price' => 5000,
        ]);

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Câble USB-C API']);

        $this->getJson('/api/v1/products/'.$product->slug)
            ->assertOk()
            ->assertJsonPath('data.sku', $product->sku);

        $added = $this->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertOk();

        $token = $added->json('data.cart_token');
        $this->assertNotEmpty($token);

        $this->withHeaders(['X-Cart-Token' => $token])
            ->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.count', 2);

        $register = $this->withHeaders(['X-Cart-Token' => $token])
            ->postJson('/api/v1/auth/register', [
                'name' => 'Awa Kouassi',
                'email' => 'awa.api@example.com',
                'phone' => '07 11 22 33 44',
                'password' => 'password123',
            ])
            ->assertCreated()
            ->assertJsonPath('user.role', UserRole::Customer->value);

        $bearer = $register->json('token');
        $this->assertNotEmpty($bearer);

        $this->withToken($bearer)
            ->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.count', 2);

        $this->withToken($bearer)
            ->postJson('/api/v1/orders', [
                'customer_name' => 'Awa Kouassi',
                'customer_phone' => '07 11 22 33 44',
                'customer_email' => 'awa.api@example.com',
                'delivery_address' => 'Cocody',
                'delivery_city' => 'Abidjan',
                'delivery_method' => 'standard',
                'payment_method' => 'cash_on_delivery',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', OrderStatus::Pending->value);

        $this->withToken($bearer)
            ->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_login_returns_a_sanctum_token(): void
    {
        $user = User::factory()->customer()->create([
            'email' => 'client@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'login' => 'client@example.com',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email']]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.email', 'client@example.com');
    }

    public function test_wishlist_requires_authentication(): void
    {
        $product = Product::factory()->inStock()->create();

        $this->postJson('/api/v1/wishlist', ['product_id' => $product->id])
            ->assertUnauthorized();

        Sanctum::actingAs(User::factory()->customer()->create());

        $this->postJson('/api/v1/wishlist', ['product_id' => $product->id])
            ->assertCreated();

        $this->getJson('/api/v1/wishlist')
            ->assertOk()
            ->assertJsonFragment(['id' => $product->id]);
    }
}
