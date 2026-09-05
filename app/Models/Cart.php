<?php

namespace App\Models;

use App\Enums\CartStatus;
use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'session_id', 'status', 'last_activity_at', 'converted_at', 'abandoned_at'])]
class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CartStatus::class,
            'last_activity_at' => 'datetime',
            'converted_at' => 'datetime',
            'abandoned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function subtotal(): int
    {
        return (int) $this->items->sum(
            fn (CartItem $item): int => $item->quantity * $item->unit_price
        );
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CartStatus::Active);
    }
}
