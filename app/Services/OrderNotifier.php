<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class OrderNotifier
{
    public function customer(Order $order, Notification $notification): void
    {
        $order->loadMissing('user');

        if ($order->user) {
            $order->user->notify($notification);

            return;
        }

        if (filled($order->customer_email)) {
            NotificationFacade::route('mail', $order->customer_email)->notify($notification);
        }
    }

    public function staff(Notification $notification): void
    {
        User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::Manager])
            ->get()
            ->each(fn (User $user) => $user->notify($notification));
    }
}
