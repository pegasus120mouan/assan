<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Concerns\HasActiveStatus;
use Database\Factories\ZoneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['city_id', 'name', 'slug', 'status', 'sort_order'])]
class Zone extends Model
{
    /** @use HasFactory<ZoneFactory> */
    use HasActiveStatus, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'sort_order' => 'integer',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }

    public function deliveryFees(): HasMany
    {
        return $this->hasMany(DeliveryFee::class);
    }
}
