<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\Status;
use App\Models\Concerns\HasActiveStatus;
use Database\Factories\DeliveryFeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'city_id',
    'commune_id',
    'zone_id',
    'delivery_method',
    'min_order_amount',
    'max_order_amount',
    'min_weight',
    'max_weight',
    'fee',
    'free_above_amount',
    'status',
])]
class DeliveryFee extends Model
{
    /** @use HasFactory<DeliveryFeeFactory> */
    use HasActiveStatus, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delivery_method' => DeliveryMethod::class,
            'status' => Status::class,
            'min_order_amount' => 'integer',
            'max_order_amount' => 'integer',
            'min_weight' => 'decimal:2',
            'max_weight' => 'decimal:2',
            'fee' => 'integer',
            'free_above_amount' => 'integer',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
