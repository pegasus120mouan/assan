<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\OrderNotifier;
use App\Services\WhatsAppService;

class SendOrderStatusChangedNotifications
{
    public function __construct(
        private readonly OrderNotifier $notifier,
        private readonly WhatsAppService $whatsapp,
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        if ($event->previous === $event->current) {
            return;
        }

        $order = $event->order->loadMissing('user');

        $this->notifier->customer($order, new OrderStatusChangedNotification($order));
        $this->whatsapp->sendDeliveryUpdate($order, $order->status->label());
    }
}
