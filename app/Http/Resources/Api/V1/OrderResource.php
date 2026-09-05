<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'payment_status' => $this->payment_status->value,
            'delivery_status' => $this->delivery_status->value,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'delivery_fee' => $this->delivery_fee,
            'total' => $this->total,
            'formatted_total' => format_price($this->total),
            'payment_method' => $this->payment_method?->value,
            'delivery_method' => $this->delivery_method?->value,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'delivery_address' => $this->delivery_address,
            'delivery_city' => $this->delivery_city,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ])),
        ];
    }
}
