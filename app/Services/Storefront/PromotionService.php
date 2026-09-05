<?php

namespace App\Services\Storefront;

use App\Enums\DiscountType;
use App\Enums\PromotionType;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class PromotionService
{
    /** @var Collection<int, Promotion>|null */
    private ?Collection $running = null;

    public function priceFor(Product $product): int
    {
        return $this->applyToAmount($product, (int) $product->selling_price);
    }

    public function applyToAmount(Product $product, int $amount): int
    {
        $promotion = $this->bestFor($product);

        if (! $promotion) {
            return $amount;
        }

        $price = match ($promotion->discount_type) {
            DiscountType::Percentage => (int) round($amount * (100 - $promotion->value) / 100),
            DiscountType::Fixed => $amount - (int) $promotion->value,
            DiscountType::PromotionalPrice => min($amount, (int) $promotion->value),
        };

        return max(0, $price);
    }

    public function bestFor(Product $product): ?Promotion
    {
        $product->loadMissing('category');

        return $this->running()
            ->filter(function (Promotion $promotion) use ($product): bool {
                return match ($promotion->type) {
                    PromotionType::Product, PromotionType::FlashSale => $promotion->product_id === $product->id
                        || ($promotion->category_id && $promotion->category_id === $product->category_id),
                    PromotionType::Category => $promotion->category_id === $product->category_id,
                };
            })
            ->sortByDesc(fn (Promotion $promotion): int => (int) $promotion->priority)
            ->first();
    }

    /**
     * @return Collection<int, Promotion>
     */
    private function running(): Collection
    {
        return $this->running ??= Promotion::query()
            ->active()
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('priority')
            ->get();
    }
}
