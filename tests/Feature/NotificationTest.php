<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\StaffOrderPlacedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_placing_an_order_notifies_the_customer_and_staff_without_http(): void
    {
        Notification::fake();
        Http::fake();

        $admin = User::factory()->admin()->create();
        $product = Product::factory()->inStock(4)->create(['selling_price' => 10000]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('checkout.store'), [
            'customer_name' => 'Awa Kouassi',
            'customer_phone' => '07 01 02 03 04',
            'customer_email' => 'awa@example.com',
            'delivery_address' => 'Riviera 2',
            'delivery_city' => 'Abidjan',
            'delivery_method' => 'standard',
            'payment_method' => 'cash_on_delivery',
        ])->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);

        Notification::assertSentOnDemand(OrderPlacedNotification::class);
        Notification::assertSentTo($admin, StaffOrderPlacedNotification::class);
        Http::assertNothingSent();
    }

    public function test_marking_payment_paid_and_cancelling_an_order_sends_notifications(): void
    {
        Notification::fake();
        Http::fake();
        config(['payment.gateways.orange_money.enabled' => true]);

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->inStock(2)->create(['selling_price' => 8000]);

        $this->actingAs($customer)
            ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'delivery_address' => 'Cocody',
                'delivery_city' => 'Abidjan',
                'delivery_method' => 'standard',
                'payment_method' => PaymentGateway::OrangeMoney->value,
            ]);

        $payment = Payment::query()->first();
        $this->actingAs($admin)
            ->patch(route('admin.payments.update', $payment), ['status' => 'paid'])
            ->assertRedirect();

        Notification::assertSentTo($customer, PaymentReceivedNotification::class);

        $order = Order::query()->first();
        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => OrderStatus::Cancelled->value])
            ->assertRedirect();

        Notification::assertSentTo($customer, OrderStatusChangedNotification::class);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'payments',
            'action' => 'Paiement mis à jour',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'orders',
            'action' => 'Commande annulée',
        ]);
        Http::assertNothingSent();
    }

    public function test_abandoned_carts_are_marked_without_sending_messages(): void
    {
        Notification::fake();
        Http::fake();

        $stale = Cart::factory()->guest()->create([
            'last_activity_at' => now()->subHours(60),
        ]);
        $fresh = Cart::factory()->guest()->create([
            'last_activity_at' => now()->subHour(),
        ]);

        $this->artisan('carts:mark-abandoned')->assertSuccessful();

        $this->assertSame(CartStatus::Abandoned, $stale->fresh()->status);
        $this->assertNotNull($stale->fresh()->abandoned_at);
        $this->assertSame(CartStatus::Active, $fresh->fresh()->status);
        Notification::assertNothingSent();
        Http::assertNothingSent();
    }
}
