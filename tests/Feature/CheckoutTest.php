<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_cart_cannot_open_checkout(): void
    {
        $this->get(route('checkout.show'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('cart');
    }

    public function test_guest_can_place_a_cash_on_delivery_order(): void
    {
        $product = Product::factory()->inStock(8)->create([
            'name' => 'Chargeur USB-C',
            'selling_price' => 12500,
        ]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->first();

        $this->assertNotNull($order);
        $this->assertSame('OVL-'.now()->year.'-000001', $order->order_number);
        $this->assertNull($order->user_id);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::CashOnDelivery, $order->payment_status);
        $this->assertSame(PaymentGateway::CashOnDelivery, $order->payment_method);
        $this->assertSame(25000, $order->subtotal);
        $this->assertSame(2000, $order->delivery_fee);
        $this->assertSame(27000, $order->total);
        $this->assertSame(2, $product->fresh()->reserved_quantity);
        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->assertSame(CartStatus::Converted, Cart::query()->first()->status);

        $response->assertRedirect(route('checkout.confirmation', $order));

        $this->get(route('checkout.confirmation', $order))
            ->assertOk()
            ->assertSee($order->order_number, false)
            ->assertSee('27 000 FCFA', false)
            ->assertSee('Chargeur USB-C', false)
            ->assertSee('Confirmer sur WhatsApp', false);
    }

    public function test_logged_in_customer_order_appears_in_account(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->inStock(3)->create(['name' => 'Enceinte OVL']);

        $this->actingAs($customer)
            ->post(route('cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $this->actingAs($customer)
            ->post(route('checkout.store'), $this->checkoutPayload([
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
            ]));

        $order = Order::query()->first();

        $this->assertSame($customer->id, $order->user_id);

        $this->actingAs($customer)
            ->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee($order->order_number, false);

        $this->actingAs($customer)
            ->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('Enceinte OVL', false)
            ->assertSee('Commander / suivre sur WhatsApp', false);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->customer()->create();
        $stranger = User::factory()->customer()->create();
        $order = Order::factory()->for($owner)->create();

        $this->actingAs($stranger)
            ->get(route('account.orders.show', $order))
            ->assertForbidden();

        $this->get(route('checkout.confirmation', $order))
            ->assertForbidden();
    }

    public function test_admin_can_list_show_and_cancel_an_order_releasing_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->inStock(5)->create();

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->first();
        $this->assertSame(3, $product->fresh()->reserved_quantity);

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($order->order_number, false);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->customer_name, false);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), [
                'status' => OrderStatus::Cancelled->value,
            ])
            ->assertSessionHas('status');

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(0, $product->fresh()->reserved_quantity);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_out_of_stock_product_cannot_be_checked_out(): void
    {
        $product = Product::factory()->inStock(1)->create();

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $product->update(['stock_quantity' => 0, 'reserved_quantity' => 0]);

        $this->from(route('checkout.show'))
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect(route('checkout.show'));

        $this->assertSame(0, Order::query()->count());
    }

    public function test_customer_cannot_access_admin_orders(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.orders.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.orders.show', $order))
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Awa Kouassi',
            'customer_phone' => '07 01 02 03 04',
            'customer_email' => 'awa@example.com',
            'delivery_address' => 'Riviera 2',
            'delivery_commune' => 'Cocody',
            'delivery_city' => 'Abidjan',
            'delivery_method' => 'standard',
            'payment_method' => 'cash_on_delivery',
        ], $overrides);
    }
}
