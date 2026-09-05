<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function accessAdmin(User $user): bool
    {
        return $user->isStaff() && $user->isActive();
    }

    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function viewStaff(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $target): bool
    {
        if ($target->isCustomer()) {
            return $user->isStaff();
        }

        return $user->isAdmin() && $target->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        if ($target->isCustomer()) {
            return $user->isStaff();
        }

        return $user->isAdmin() && $target->isStaff();
    }

    public function delete(User $user, User $target): bool
    {
        return $user->isAdmin()
            && $target->isStaff()
            && $user->isNot($target);
    }
}
