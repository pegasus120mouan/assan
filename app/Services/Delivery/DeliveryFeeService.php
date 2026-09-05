<?php

namespace App\Services\Delivery;

use App\Enums\DeliveryMethod;
use App\Models\Cart;
use App\Models\Commune;
use App\Models\DeliveryFee;

class DeliveryFeeService
{
    public function quote(?Commune $commune, DeliveryMethod $method, int $subtotal, float $weight = 0): int
    {
        if ($method === DeliveryMethod::Pickup) {
            return 0;
        }

        $fallback = (int) config('delivery.standard_fee', 2000);

        $matched = DeliveryFee::query()
            ->active()
            ->where('delivery_method', $method)
            ->get()
            ->filter(fn (DeliveryFee $fee): bool => $this->matches($fee, $commune, $subtotal, $weight))
            ->sortByDesc(fn (DeliveryFee $fee): int => $this->specificity($fee))
            ->first();

        if (! $matched) {
            return $fallback;
        }

        if ($matched->free_above_amount && $subtotal >= (int) $matched->free_above_amount) {
            return 0;
        }

        return (int) $matched->fee;
    }

    public function quoteCart(Cart $cart, ?Commune $commune, DeliveryMethod $method): int
    {
        $weight = (float) $cart->items->sum(
            fn ($item): float => (float) ($item->product?->weight ?? 0) * (int) $item->quantity
        );

        return $this->quote($commune, $method, $cart->subtotal(), $weight);
    }

    private function matches(DeliveryFee $fee, ?Commune $commune, int $subtotal, float $weight): bool
    {
        if ($subtotal < (int) $fee->min_order_amount) {
            return false;
        }

        if ($fee->max_order_amount !== null && $subtotal > (int) $fee->max_order_amount) {
            return false;
        }

        if ($fee->min_weight !== null && $weight < (float) $fee->min_weight) {
            return false;
        }

        if ($fee->max_weight !== null && $weight > (float) $fee->max_weight) {
            return false;
        }

        if ($fee->commune_id) {
            return $commune?->id === $fee->commune_id;
        }

        if ($fee->zone_id) {
            return $commune?->zone_id === $fee->zone_id;
        }

        if ($fee->city_id) {
            return $commune?->city_id === $fee->city_id;
        }

        return true;
    }

    private function specificity(DeliveryFee $fee): int
    {
        if ($fee->commune_id) {
            return 3;
        }

        if ($fee->zone_id) {
            return 2;
        }

        if ($fee->city_id) {
            return 1;
        }

        return 0;
    }
}
