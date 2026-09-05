<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function isReady(): bool
    {
        return (bool) config('whatsapp.enabled')
            && filled(config('whatsapp.api_url'))
            && filled(config('whatsapp.access_token'))
            && filled(config('whatsapp.phone_number_id'));
    }

    public function sendOrderConfirmation(Order $order): bool
    {
        return $this->queue('order_confirmation', $order->customer_phone, [
            'order' => $order->order_number,
            'total' => $order->total,
        ]);
    }

    public function sendPaymentConfirmation(Order $order): bool
    {
        return $this->queue('payment_confirmation', $order->customer_phone, [
            'order' => $order->order_number,
        ]);
    }

    public function sendDeliveryUpdate(Order $order, string $status): bool
    {
        return $this->queue('delivery_update', $order->customer_phone, [
            'order' => $order->order_number,
            'status' => $status,
        ]);
    }

    public function sendCustomerMessage(string $phone, string $message): bool
    {
        return $this->queue('customer_message', $phone, ['message' => $message]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function queue(string $type, ?string $phone, array $payload): bool
    {
        if (! $this->isReady() || blank($phone)) {
            return false;
        }

        Log::info('WhatsApp message prepared (API réelle non branchée).', [
            'type' => $type,
            'phone' => $phone,
            'payload' => $payload,
        ]);

        return false;
    }
}
