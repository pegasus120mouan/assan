<?php

namespace App\Services\Storefront;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Events\OrderPlaced;
use App\Models\Cart;
use App\Models\Commune;
use App\Models\Order;
use App\Services\Delivery\DeliveryFeeService;
use App\Services\Delivery\DeliveryService;
use App\Services\Payments\PaymentService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $carts,
        private readonly StockService $stock,
        private readonly CouponService $coupons,
        private readonly DeliveryFeeService $fees,
        private readonly DeliveryService $deliveries,
        private readonly PaymentService $payments,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function place(array $payload, ?Cart $cart = null): Order
    {
        $cart ??= $this->carts->find();

        if (! $cart || $cart->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Votre panier est vide.',
            ]);
        }

        $cart->loadMissing(['items.product.variants', 'items.variant', 'items.product.category']);

        return DB::transaction(function () use ($cart, $payload): Order {
            $subtotal = $cart->subtotal();
            $method = DeliveryMethod::from((string) $payload['delivery_method']);
            $commune = ! empty($payload['delivery_commune_id'])
                ? Commune::query()->find($payload['delivery_commune_id'])
                : null;
            $deliveryFee = $this->fees->quoteCart($cart, $commune, $method);

            if (! empty($payload['coupon_code'])) {
                $this->coupons->apply((string) $payload['coupon_code'], $subtotal, auth()->user());
            }

            $discount = $this->coupons->discountFor($subtotal, auth()->user());
            $total = max(0, $subtotal - $discount + $deliveryFee);
            $gateway = PaymentGateway::from((string) $payload['payment_method']);

            $order = Order::query()->create([
                'order_number' => $this->nextOrderNumber(),
                'user_id' => auth()->id(),
                'coupon_id' => $this->coupons->current()?->id,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'delivery_status' => DeliveryStatus::Pending,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'payment_method' => $gateway,
                'delivery_method' => $method,
                'customer_name' => $payload['customer_name'],
                'customer_phone' => $payload['customer_phone'],
                'customer_email' => $payload['customer_email'] ?? null,
                'delivery_address' => $payload['delivery_address'],
                'delivery_commune_id' => $commune?->id,
                'delivery_commune' => $commune?->name ?? ($payload['delivery_commune'] ?? null),
                'delivery_city' => $payload['delivery_city'],
                'customer_notes' => $payload['customer_notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                if (! $item->product?->isActive()) {
                    throw ValidationException::withMessages([
                        'cart' => 'Un article de votre panier n’est plus disponible.',
                    ]);
                }

                $this->stock->reserve($item->product, $item->quantity, $item->variant);

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'variant_name' => $item->variant?->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal(),
                ]);
            }

            $this->payments->charge($order, $gateway, $total);
            $this->deliveries->createForOrder($order->fresh());

            if ($discount > 0) {
                $this->coupons->redeem($order, $subtotal);
            } else {
                $this->coupons->forget();
            }

            $this->carts->markConverted($cart);

            $order = $order->load(['items', 'payments', 'delivery']);

            DB::afterCommit(function () use ($order): void {
                OrderPlaced::dispatch($order->fresh(['items', 'payments', 'delivery', 'user']) ?? $order);
            });

            return $order;
        });
    }

    private function nextOrderNumber(): string
    {
        $year = now()->year;
        $prefix = 'OVL-'.$year.'-';
        $last = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
