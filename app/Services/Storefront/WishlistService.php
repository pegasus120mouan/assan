<?php

namespace App\Services\Storefront;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;

class WishlistService
{
    /**
     * @return list<int>
     */
    public function productIds(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $key = 'wishlist.product_ids.'.$user->id;

        if (app()->bound($key)) {
            return app($key);
        }

        $ids = $user->wishlists()->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        app()->instance($key, $ids);

        return $ids;
    }

    public function has(User $user, Product $product): bool
    {
        return $user->wishlists()->where('product_id', $product->id)->exists();
    }

    public function toggle(User $user, Product $product): bool
    {
        $existing = $user->wishlists()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();

            app()->forgetInstance('wishlist.product_ids.'.$user->id);

            return false;
        }

        Wishlist::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        app()->forgetInstance('wishlist.product_ids.'.$user->id);

        return true;
    }
}
