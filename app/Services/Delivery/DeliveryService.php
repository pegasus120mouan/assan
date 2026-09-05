<?php

namespace App\Services\Delivery;

use App\Contracts\Delivery\DeliveryProviderInterface;
use App\Enums\DeliveryProvider;
use App\Enums\DeliveryStatus;
use App\Events\DeliveryStatusChanged;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\Delivery\Providers\InternalDeliveryProvider;
use App\Services\Delivery\Providers\OvlDeliveryProvider;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    public function createForOrder(Order $order): Delivery
    {
        $provider = $this->driver();

        $delivery = Delivery::query()->create([
            'order_id' => $order->id,
            'tracking_number' => 'DLV-'.$order->order_number,
            'provider' => $provider->key(),
            'status' => DeliveryStatus::Pending,
            'delivery_fee' => (int) $order->delivery_fee,
            'metadata' => [],
        ]);

        $provider->dispatch($delivery);

        $order->update([
            'delivery_status' => DeliveryStatus::Pending,
        ]);

        return $delivery->refresh();
    }

    public function updateStatus(Delivery $delivery, DeliveryStatus $status, ?int $assignedTo = null, ?string $failureReason = null): Delivery
    {
        $previous = $delivery->status;

        $delivery->update([
            'status' => $status,
            'assigned_to' => $assignedTo ?? $delivery->assigned_to,
            'failure_reason' => $failureReason,
            'picked_up_at' => $status === DeliveryStatus::PickedUp ? ($delivery->picked_up_at ?? now()) : $delivery->picked_up_at,
            'delivered_at' => $status === DeliveryStatus::Delivered ? ($delivery->delivered_at ?? now()) : $delivery->delivered_at,
        ]);

        $delivery->order?->update([
            'delivery_status' => $status,
        ]);

        $delivery = $delivery->refresh()->load('order');

        DB::afterCommit(function () use ($delivery, $previous, $status): void {
            DeliveryStatusChanged::dispatch($delivery->fresh(['order.user']) ?? $delivery, $previous, $status);
        });

        return $delivery;
    }

    public function driver(): DeliveryProviderInterface
    {
        $key = DeliveryProvider::tryFrom((string) config('delivery.default', 'internal'))
            ?? DeliveryProvider::Internal;

        return match ($key) {
            DeliveryProvider::OvlDelivery => new OvlDeliveryProvider,
            default => new InternalDeliveryProvider,
        };
    }
}
