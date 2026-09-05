<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\PromotionType;
use App\Enums\Status;
use App\Models\Concerns\HasActiveStatus;
use Database\Factories\PromotionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'slug',
    'type',
    'discount_type',
    'value',
    'product_id',
    'category_id',
    'starts_at',
    'expires_at',
    'status',
    'priority',
])]
class Promotion extends Model
{
    /** @use HasFactory<PromotionFactory> */
    use HasActiveStatus, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PromotionType::class,
            'discount_type' => DiscountType::class,
            'status' => Status::class,
            'value' => 'integer',
            'priority' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isRunning(): bool
    {
        $started = $this->starts_at === null || $this->starts_at->isPast();
        $notExpired = $this->expires_at === null || $this->expires_at->isFuture();

        return $this->isActive() && $started && $notExpired;
    }
}
