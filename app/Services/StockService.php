<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    public function getAvailableStock(Product $product, ?ProductVariant $variant = null): int
    {
        if ($variant) {
            return $variant->availableStock();
        }

        if ($product->variants()->exists()) {
            $product->loadMissing('variants');

            return $product->availableStock();
        }

        return $product->availableStock();
    }

    public function assertAvailable(Product $product, int $quantity, ?ProductVariant $variant = null): void
    {
        $available = $this->getAvailableStock($product, $variant);

        if ($quantity > $available) {
            throw new InsufficientStockException($quantity, $available);
        }
    }

    public function increaseStock(
        Product $product,
        int $quantity,
        StockMovementType $type = StockMovementType::Purchase,
        ?ProductVariant $variant = null,
        ?string $reason = null,
        ?Model $reference = null,
    ): StockMovement {
        $this->assertPositive($quantity);

        if (! $type->increasesStock()) {
            throw new InvalidArgumentException('Ce type de mouvement n\'augmente pas le stock.');
        }

        return $this->apply($product, $quantity, $type, $variant, $reason, $reference);
    }

    public function decreaseStock(
        Product $product,
        int $quantity,
        StockMovementType $type = StockMovementType::Sale,
        ?ProductVariant $variant = null,
        ?string $reason = null,
        ?Model $reference = null,
    ): StockMovement {
        $this->assertPositive($quantity);

        if (! $type->decreasesStock()) {
            throw new InvalidArgumentException('Ce type de mouvement ne diminue pas le stock.');
        }

        return $this->apply($product, -$quantity, $type, $variant, $reason, $reference);
    }

    public function adjustStock(
        Product $product,
        int $newQuantity,
        ?ProductVariant $variant = null,
        ?string $reason = null,
    ): StockMovement {
        if ($newQuantity < 0) {
            throw new InvalidArgumentException('Le stock ne peut pas être négatif.');
        }

        return DB::transaction(function () use ($product, $newQuantity, $variant, $reason): StockMovement {
            [$product, $variant] = $this->lock($product, $variant);
            $current = (int) $this->stockable($product, $variant)->stock_quantity;
            $delta = $newQuantity - $current;

            if ($delta === 0) {
                throw new InvalidArgumentException('Le stock est déjà à cette quantité.');
            }

            $message = trim(($reason ? $reason.' — ' : '').'Ajustement : '.$current.' → '.$newQuantity);

            return $this->write($product, $delta, StockMovementType::Adjustment, $variant, $message, null);
        });
    }

    public function reserve(Product $product, int $quantity, ?ProductVariant $variant = null): void
    {
        $this->assertPositive($quantity);

        DB::transaction(function () use ($product, $quantity, $variant): void {
            [$product, $variant] = $this->lock($product, $variant);
            $this->assertAvailable($product, $quantity, $variant);
            $stockable = $this->stockable($product, $variant);
            $stockable->increment('reserved_quantity', $quantity);
        });
    }

    public function release(Product $product, int $quantity, ?ProductVariant $variant = null): void
    {
        $this->assertPositive($quantity);

        DB::transaction(function () use ($product, $quantity, $variant): void {
            [$product, $variant] = $this->lock($product, $variant);
            $stockable = $this->stockable($product, $variant);
            $reserved = (int) $stockable->reserved_quantity;

            if ($quantity > $reserved) {
                throw new InsufficientStockException($quantity, $reserved, 'Quantité réservée insuffisante.');
            }

            $stockable->decrement('reserved_quantity', $quantity);
        });
    }

    private function apply(
        Product $product,
        int $delta,
        StockMovementType $type,
        ?ProductVariant $variant,
        ?string $reason,
        ?Model $reference,
    ): StockMovement {
        return DB::transaction(function () use ($product, $delta, $type, $variant, $reason, $reference): StockMovement {
            [$product, $variant] = $this->lock($product, $variant);

            return $this->write($product, $delta, $type, $variant, $reason, $reference);
        });
    }

    private function write(
        Product $product,
        int $delta,
        StockMovementType $type,
        ?ProductVariant $variant,
        ?string $reason,
        ?Model $reference,
    ): StockMovement {
        $stockable = $this->stockable($product, $variant);

        if ($delta < 0) {
            $available = max(0, (int) $stockable->stock_quantity - (int) $stockable->reserved_quantity);

            if (abs($delta) > $available) {
                throw new InsufficientStockException(abs($delta), $available);
            }
        }

        $newStock = (int) $stockable->stock_quantity + $delta;
        $stockable->update(['stock_quantity' => $newStock]);

        return StockMovement::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'type' => $type,
            'quantity' => abs($delta),
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'reason' => $reason,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * @return array{0: Product, 1: ProductVariant|null}
     */
    private function lock(Product $product, ?ProductVariant $variant): array
    {
        $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);

        if ($variant) {
            $lockedVariant = ProductVariant::query()
                ->where('product_id', $lockedProduct->id)
                ->lockForUpdate()
                ->findOrFail($variant->id);

            return [$lockedProduct, $lockedVariant];
        }

        if ($lockedProduct->variants()->exists()) {
            throw new InvalidArgumentException('Choisissez une variante pour ce produit.');
        }

        return [$lockedProduct, null];
    }

    private function stockable(Product $product, ?ProductVariant $variant): Product|ProductVariant
    {
        return $variant ?? $product;
    }

    private function assertPositive(int $quantity): void
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('La quantité doit être supérieure à 0.');
        }
    }
}
