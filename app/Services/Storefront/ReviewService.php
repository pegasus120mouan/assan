<?php

namespace App\Services\Storefront;

use App\Enums\ReviewStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function canReview(User $user, Product $product): bool
    {
        if ($this->existing($user, $product)) {
            return false;
        }

        return $this->purchaseOrder($user, $product) !== null;
    }

    public function existing(User $user, Product $product): ?Review
    {
        return Review::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(User $user, Product $product, array $payload): Review
    {
        $order = $this->purchaseOrder($user, $product);

        if (! $order) {
            throw ValidationException::withMessages([
                'product' => 'Seuls les clients ayant acheté ce produit peuvent laisser un avis.',
            ]);
        }

        if ($this->existing($user, $product)) {
            throw ValidationException::withMessages([
                'product' => 'Vous avez déjà laissé un avis sur ce produit.',
            ]);
        }

        return Review::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => (int) $payload['rating'],
            'title' => $payload['title'] ?? null,
            'comment' => $payload['comment'] ?? null,
            'is_verified' => true,
            'status' => ReviewStatus::Pending,
        ]);
    }

    public function moderate(Review $review, ReviewStatus $status): Review
    {
        $review->update(['status' => $status]);

        return $review->refresh();
    }

    private function purchaseOrder(User $user, Product $product): ?Order
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->notCancelled()
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->latest('id')
            ->first();
    }
}
