<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\StaffOrderPlacedNotification;
use App\Services\OrderNotifier;
use App\Services\WhatsAppService;

class SendOrderPlacedNotifications
{
    public function __construct(
        private readonly OrderNotifier $notifier,
        private readonly WhatsAppService $whatsapp,
    ) {}

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->loadMissing(['user', 'items']);

        $this->notifier->customer($order, new OrderPlacedNotification($order));
        $this->notifier->staff(new StaffOrderPlacedNotification($order));
        $this->whatsapp->sendOrderConfirmation($order);
    }
}
