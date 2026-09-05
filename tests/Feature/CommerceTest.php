<?php

namespace Tests\Feature;

use App\Enums\CouponType;
use App\Enums\DiscountType;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\PromotionType;
use App\Enums\ReviewStatus;
use App\Enums\Status;
use App\Models\Commune;
use App\Models\Coupon;
use App\Models\Delivery;
use App\Models\DeliveryFee;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CommerceTest extends TestCase
{
    use RefreshDatabase;

    public function test_orange_money_stub_creates_pending_payment_without_http(): void
    {
        Http::fake();
        config(['payment.gateways.orange_money.enabled' => true]);

        $product = Product::factory()->inStock(3)->create(['selling_price' => 10000]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('checkout.store'), $this->checkoutPayload([
            'payment_method' => PaymentGateway::OrangeMoney->value,
        ]))->assertRedirect();

        $payment = Payment::query()->first();

        $this->assertNotNull($payment);
        $this->assertSame(PaymentGateway::OrangeMoney, $payment->gateway);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame('stub', $payment->metadata['mode'] ?? null);
        Http::assertNothingSent();
        $this->assertTrue(Delivery::query()->exists());
    }

    public function test_disabled_mobile_money_cannot_be_selected(): void
    {
        $product = Product::factory()->inStock(2)->create();

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->from(route('checkout.show'))
            ->post(route('checkout.store'), $this->checkoutPayload([
                'payment_method' => PaymentGateway::Wave->value,
            ]))
            ->assertRedirect(route('checkout.show'))
            ->assertSessionHasErrors('payment_method');
    }

    public function test_admin_can_mark_a_payment_as_paid(): void
    {
        Http::fake();
        config(['payment.gateways.mtn_money.enabled' => true]);
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->inStock(2)->create();

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('checkout.store'), $this->checkoutPayload([
            'payment_method' => PaymentGateway::MtnMoney->value,
        ]));

        $payment = Payment::query()->first();

        $this->actingAs($admin)
            ->patch(route('admin.payments.update', $payment), ['status' => 'paid'])
            ->assertSessionHas('status');

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(PaymentStatus::Paid, $payment->order->fresh()->payment_status);
    }

    public function test_delivery_fee_uses_commune_then_falls_back_and_can_be_free(): void
    {
        $product = Product::factory()->inStock(20)->create(['selling_price' => 10000]);
        $commune = Commune::factory()->create(['name' => 'Cocody']);
        DeliveryFee::factory()->create([
            'commune_id' => $commune->id,
            'fee' => 2500,
            'free_above_amount' => 50000,
            'min_order_amount' => 0,
        ]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->post(route('checkout.store'), $this->checkoutPayload([
            'delivery_commune_id' => $commune->id,
        ]));

        $this->assertSame(2500, Order::query()->first()->delivery_fee);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 5]);
        $this->post(route('checkout.store'), $this->checkoutPayload([
            'delivery_commune_id' => $commune->id,
        ]));

        $this->assertSame(0, Order::query()->latest('id')->first()->delivery_fee);
    }

    public function test_coupon_applies_at_checkout_and_records_usage(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->inStock(5)->create(['selling_price' => 20000]);
        $coupon = Coupon::factory()->create([
            'code' => 'OVL10',
            'type' => CouponType::Percentage,
            'value' => 10,
            'minimum_order_amount' => 10000,
            'maximum_discount' => null,
            'usage_per_customer' => 1,
        ]);

        $this->actingAs($customer)
            ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($customer)
            ->post(route('cart.coupon.apply'), ['code' => 'ovl10'])
            ->assertSessionHas('status');

        $this->actingAs($customer)
            ->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->first();
        $this->assertSame(2000, $order->discount_amount);
        $this->assertSame(20000, $order->subtotal);
        $this->assertSame(20000, $order->total);
        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'user_id' => $customer->id,
        ]);
    }

    public function test_expired_coupon_is_rejected(): void
    {
        $product = Product::factory()->inStock(2)->create(['selling_price' => 15000]);
        Coupon::factory()->expired()->create(['code' => 'OLD']);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->from(route('cart.index'))
            ->post(route('cart.coupon.apply'), ['code' => 'OLD'])
            ->assertSessionHasErrors('code');
    }

    public function test_product_promotion_changes_storefront_and_cart_price(): void
    {
        $product = Product::factory()->inStock(4)->create(['selling_price' => 10000]);
        Promotion::factory()->create([
            'type' => PromotionType::Product,
            'discount_type' => DiscountType::Percentage,
            'value' => 10,
            'product_id' => $product->id,
            'category_id' => null,
            'status' => Status::Active,
        ]);

        $this->assertSame(9000, $product->fresh()->currentPrice());

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'unit_price' => 9000,
        ]);
    }

    public function test_only_a_buyer_can_review_and_admin_must_approve(): void
    {
        $buyer = User::factory()->customer()->create();
        $stranger = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->inStock(3)->create(['name' => 'Cable OVL']);

        $this->actingAs($stranger)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Super.',
            ])
            ->assertSessionHasErrors('product');

        $this->actingAs($buyer)
            ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($buyer)
            ->post(route('checkout.store'), $this->checkoutPayload());

        $this->actingAs($buyer)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'title' => 'Très bon produit',
                'comment' => 'Livraison rapide.',
            ])
            ->assertSessionHas('status');

        $review = Review::query()->first();
        $this->assertTrue($review->is_verified);
        $this->assertSame(ReviewStatus::Pending, $review->status);

        $this->get(route('catalog.product', $product))
            ->assertOk()
            ->assertDontSee('Très bon produit', false);

        $this->actingAs($admin)
            ->patch(route('admin.reviews.update', $review), ['status' => ReviewStatus::Approved->value])
            ->assertSessionHas('status');

        $this->get(route('catalog.product', $product))
            ->assertOk()
            ->assertSee('Très bon produit', false);
    }

    public function test_customer_cannot_open_admin_commerce_modules(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->get(route('admin.payments.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.deliveries.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.coupons.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.reviews.index'))->assertForbidden();
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
