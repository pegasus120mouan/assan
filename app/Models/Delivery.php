<?php

namespace App\Models;

use App\Enums\DeliveryProvider;
use App\Enums\DeliveryStatus;
use Database\Factories\DeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'tracking_number',
    'provider',
    'status',
    'assigned_to',
    'delivery_fee',
    'picked_up_at',
    'delivered_at',
    'failure_reason',
    'metadata',
])]
class Delivery extends Model
{
    /** @use HasFactory<DeliveryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provider' => DeliveryProvider::class,
            'status' => DeliveryStatus::class,
            'delivery_fee' => 'integer',
            'metadata' => 'array',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
