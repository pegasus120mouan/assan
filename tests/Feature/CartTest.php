<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\Status;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_browsing_the_storefront_does_not_create_an_empty_cart(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('cart.index'))->assertOk()->assertSee('Votre panier est vide.', false);

        $this->assertDatabaseCount('carts', 0);
    }

    public function test_guest_can_add_a_product_to_the_cart(): void
    {
        $product = Product::factory()->inStock(8)->create([
            'name' => 'Powerbank 20 000 mAh',
            'selling_price' => 12500,
            'status' => Status::Active,
        ]);

        $this->from(route('catalog.product', $product))
            ->post(route('cart.add'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('catalog.product', $product))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 12500,
        ]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Powerbank 20 000 mAh', false)
            ->assertSee('12 500 FCFA', false);
    }

    public function test_adding_the_same_product_increases_the_existing_line(): void
    {
        $product = Product::factory()->inStock(10)->create(['selling_price' => 5000]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2]);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(3, CartItem::query()->first()->quantity);
    }

    public function test_buy_intent_redirects_to_checkout(): void
    {
        $product = Product::factory()->inStock(4)->create();

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'intent' => 'buy',
        ])->assertRedirect(route('checkout.show'));
    }

    public function test_variant_is_required_when_the_product_has_variants(): void
    {
        $product = Product::factory()->inStock(0)->create(['stock_quantity' => 0]);
        ProductVariant::factory()->for($product)->create(['stock_quantity' => 4]);

        $this->from(route('catalog.product', $product))
            ->post(route('cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertRedirect(route('catalog.product', $product))
            ->assertSessionHasErrors('product_variant_id');
    }

    public function test_quantity_cannot_exceed_available_stock(): void
    {
        $product = Product::factory()->inStock(2)->create();

        $this->from(route('cart.index'))
            ->post(route('cart.add'), [
                'product_id' => $product->id,
                'quantity' => 5,
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_cart_is_merged_on_login(): void
    {
        $product = Product::factory()->inStock(6)->create();
        $user = User::factory()->customer()->create([
            'password' => 'password123',
        ]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => null,
            'status' => CartStatus::Active->value,
        ]);

        $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('account.dashboard'));

        $this->assertTrue(
            Cart::query()->where('user_id', $user->id)->active()->exists()
        );
        $this->assertSame(
            2,
            (int) CartItem::query()
                ->whereHas('cart', fn ($query) => $query->where('user_id', $user->id)->active())
                ->sum('quantity')
        );
        $this->assertFalse(
            Cart::query()->whereNull('user_id')->active()->exists()
        );
    }

    public function test_guest_cart_is_merged_on_register(): void
    {
        $product = Product::factory()->inStock(5)->create();

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->post(route('register'), [
            'name' => 'Awa Kouassi',
            'email' => 'awa-panier@example.com',
            'phone' => '07 11 22 33 44',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('account.dashboard'));

        $user = User::query()->where('email', 'awa-panier@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(
            1,
            (int) CartItem::query()
                ->whereHas('cart', fn ($query) => $query->where('user_id', $user->id)->active())
                ->sum('quantity')
        );
    }

    public function test_user_can_update_and_remove_cart_items(): void
    {
        $product = Product::factory()->inStock(10)->create();

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $item = CartItem::query()->first();

        $this->patch(route('cart.update', $item), ['quantity' => 4])
            ->assertSessionHas('status');
        $this->assertSame(4, $item->fresh()->quantity);

        $this->delete(route('cart.remove', $item))->assertSessionHas('status');
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_another_users_cart_item_cannot_be_updated(): void
    {
        $owner = User::factory()->customer()->create();
        $stranger = User::factory()->customer()->create();
        $product = Product::factory()->inStock(5)->create();

        $this->actingAs($owner)
            ->post(route('cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $item = CartItem::query()->first();

        $this->actingAs($stranger)
            ->patch(route('cart.update', $item), ['quantity' => 3])
            ->assertNotFound();
    }
}
