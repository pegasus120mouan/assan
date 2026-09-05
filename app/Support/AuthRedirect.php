<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AuthRedirect
{
    public static function for(?User $user): RedirectResponse
    {
        if ($user?->isStaff()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('account.dashboard'));
    }

    public static function homeUrl(?User $user): string
    {
        if ($user?->isStaff()) {
            return route('admin.dashboard');
        }

        return route('account.dashboard');
    }
}
