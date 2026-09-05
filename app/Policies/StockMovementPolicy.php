<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class StockMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function create(User $user, ?Product $product = null): bool
    {
        return $user->isStaff();
    }
}
