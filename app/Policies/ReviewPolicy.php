<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Review $review): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isStaff();
    }
}
