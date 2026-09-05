<?php

namespace App\Listeners;

use App\Events\DeliveryStatusChanged;
use App\Notifications\DeliveryStatusChangedNotification;
use App\Services\OrderNotifier;
use App\Services\WhatsAppService;

class SendDeliveryStatusChangedNotifications
{
    public function __construct(
        private readonly OrderNotifier $notifier,
        private readonly WhatsAppService $whatsapp,
    ) {}

    public function handle(DeliveryStatusChanged $event): void
    {
        if ($event->previous === $event->current) {
            return;
        }

        $delivery = $event->delivery->loadMissing('order.user');
        $order = $delivery->order;

        if (! $order) {
            return;
        }

        $this->notifier->customer($order, new DeliveryStatusChangedNotification($order, $delivery));
        $this->whatsapp->sendDeliveryUpdate($order, $delivery->status->label());
    }
}
