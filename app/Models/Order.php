<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'order_number',
    'user_id',
    'coupon_id',
    'status',
    'payment_status',
    'delivery_status',
    'subtotal',
    'discount_amount',
    'delivery_fee',
    'total',
    'payment_method',
    'delivery_method',
    'customer_name',
    'customer_phone',
    'customer_email',
    'delivery_address',
    'delivery_commune_id',
    'delivery_commune',
    'delivery_city',
    'customer_notes',
    'confirmed_at',
    'cancelled_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'delivery_status' => DeliveryStatus::class,
            'payment_method' => PaymentGateway::class,
            'delivery_method' => DeliveryMethod::class,
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'delivery_fee' => 'integer',
            'total' => 'integer',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'delivery_commune_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->whereNotIn('status', [OrderStatus::Cancelled, OrderStatus::Returned]);
    }

    public function scopeCountedAsRevenue(Builder $query): Builder
    {
        return $query->notCancelled()->whereIn('payment_status', [
            PaymentStatus::Paid,
            PaymentStatus::CashOnDelivery,
        ]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }
}
