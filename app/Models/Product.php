<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Concerns\HasActiveStatus;
use App\Services\Storefront\PromotionService;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

#[Fillable([
    'category_id',
    'brand_id',
    'name',
    'slug',
    'sku',
    'short_description',
    'description',
    'purchase_price',
    'selling_price',
    'compare_price',
    'cost_price',
    'stock_quantity',
    'reserved_quantity',
    'low_stock_threshold',
    'weight',
    'status',
    'featured',
    'is_new',
    'is_best_seller',
    'meta_title',
    'meta_description',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasActiveStatus, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'purchase_price' => 'integer',
            'selling_price' => 'integer',
            'compare_price' => 'integer',
            'cost_price' => 'integer',
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'weight' => 'decimal:2',
            'featured' => 'boolean',
            'is_new' => 'boolean',
            'is_best_seller' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function coverUrl(): ?string
    {
        return ($this->primaryImage ?: $this->images->first())?->url();
    }

    /**
     * @return Collection<int, array{label: string|null, text: string}>
     */
    public function featureLines(): Collection
    {
        return collect(preg_split('/\R/u', (string) $this->short_description) ?: [])
            ->map(fn (string $line): string => trim($line, " \t-•"))
            ->filter()
            ->map(function (string $line): array {
                if (preg_match('/^(.+?)\s*[:：]\s*(.+)$/u', $line, $matches)) {
                    return ['label' => $matches[1], 'text' => $matches[2]];
                }

                return ['label' => null, 'text' => $line];
            })
            ->values();
    }

    public function availableStock(): int
    {
        if ($this->variants->isNotEmpty()) {
            return (int) $this->variants->sum(
                fn (ProductVariant $variant): int => $variant->availableStock()
            );
        }

        return max(0, (int) $this->stock_quantity - (int) $this->reserved_quantity);
    }

    public function isInStock(): bool
    {
        return $this->availableStock() > 0;
    }

    public function isOutOfStock(): bool
    {
        return ! $this->isInStock();
    }

    public function isLowStock(): bool
    {
        return $this->isInStock() && $this->availableStock() <= (int) $this->low_stock_threshold;
    }

    public function currentPrice(): int
    {
        return app(PromotionService::class)->priceFor($this);
    }

    public function displayComparePrice(): ?int
    {
        $current = $this->currentPrice();

        if ($this->compare_price !== null && (int) $this->compare_price > $current) {
            return (int) $this->compare_price;
        }

        if ($current < (int) $this->selling_price) {
            return (int) $this->selling_price;
        }

        return null;
    }

    public function isOnSale(): bool
    {
        return $this->displayComparePrice() !== null;
    }

    public function discountPercent(): ?int
    {
        $compare = $this->displayComparePrice();

        if ($compare === null || $compare <= 0) {
            return null;
        }

        return (int) round((($compare - $this->currentPrice()) / $compare) * 100);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->active();
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeNewArrivals(Builder $query): Builder
    {
        return $query->where('is_new', true);
    }

    public function scopeBestSellers(Builder $query): Builder
    {
        return $query->where('is_best_seller', true);
    }

    public function scopeOnSale(Builder $query): Builder
    {
        return $query->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'selling_price');
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            $builder->whereHas('variants', function (Builder $variants): void {
                $variants->whereRaw('(stock_quantity - reserved_quantity) > 0');
            })->orWhere(function (Builder $withoutVariants): void {
                $withoutVariants->whereDoesntHave('variants')
                    ->whereRaw('(stock_quantity - reserved_quantity) > 0');
            });
        });
    }
}
