<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Enums\Status;
use App\Models\Concerns\HasActiveStatus;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'type',
    'value',
    'minimum_order_amount',
    'maximum_discount',
    'starts_at',
    'expires_at',
    'usage_limit',
    'usage_per_customer',
    'used_count',
    'status',
])]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasActiveStatus, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'status' => Status::class,
            'value' => 'integer',
            'minimum_order_amount' => 'integer',
            'maximum_discount' => 'integer',
            'usage_limit' => 'integer',
            'usage_per_customer' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    public function isUsable(): bool
    {
        return $this->isActive()
            && $this->isStarted()
            && ! $this->isExpired()
            && ! $this->hasReachedUsageLimit();
    }
}
