<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Notifications\PaymentReceivedNotification;
use App\Services\OrderNotifier;
use App\Services\WhatsAppService;

class SendPaymentReceivedNotifications
{
    public function __construct(
        private readonly OrderNotifier $notifier,
        private readonly WhatsAppService $whatsapp,
    ) {}

    public function handle(PaymentReceived $event): void
    {
        $order = $event->order->loadMissing('user');

        $this->notifier->customer($order, new PaymentReceivedNotification($order, $event->payment));
        $this->whatsapp->sendPaymentConfirmation($order);
    }
}
