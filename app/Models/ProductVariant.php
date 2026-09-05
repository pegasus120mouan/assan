<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Concerns\HasActiveStatus;
use App\Services\Storefront\PromotionService;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_id',
    'name',
    'sku',
    'price',
    'stock_quantity',
    'reserved_quantity',
    'options',
    'status',
])]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasActiveStatus, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'options' => 'array',
            'status' => Status::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function availableStock(): int
    {
        return max(0, (int) $this->stock_quantity - (int) $this->reserved_quantity);
    }

    public function effectivePrice(): int
    {
        $base = $this->price ?? (int) $this->product->selling_price;

        return app(PromotionService::class)->applyToAmount($this->product, $base);
    }
}
