<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_service_increases_and_decreases_product_stock(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'reserved_quantity' => 0,
        ]);
        $stock = app(StockService::class);

        $stock->increaseStock($product, 5, StockMovementType::Purchase, null, 'Réassort');
        $this->assertSame(15, $product->fresh()->stock_quantity);

        $stock->decreaseStock($product->fresh(), 3, StockMovementType::Sale);
        $this->assertSame(12, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovementType::Sale->value,
            'quantity' => 3,
        ]);
    }

    public function test_stock_service_rejects_insufficient_stock(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 2,
            'reserved_quantity' => 0,
        ]);

        $this->expectException(InsufficientStockException::class);

        app(StockService::class)->decreaseStock($product, 5, StockMovementType::Sale);
    }

    public function test_reserved_quantity_reduces_available_stock(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'reserved_quantity' => 0,
        ]);
        $stock = app(StockService::class);

        $stock->reserve($product, 4);
        $this->assertSame(4, $product->fresh()->reserved_quantity);
        $this->assertSame(6, $stock->getAvailableStock($product->fresh()));

        $this->expectException(InsufficientStockException::class);
        $stock->decreaseStock($product->fresh(), 7, StockMovementType::Sale);
    }

    public function test_variant_stock_is_required_when_variants_exist(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);
        $variant = ProductVariant::factory()->for($product)->create(['stock_quantity' => 5]);

        app(StockService::class)->increaseStock($product, 2, StockMovementType::Purchase, $variant);

        $this->assertSame(7, $variant->fresh()->stock_quantity);
        $this->assertSame(7, app(StockService::class)->getAvailableStock($product->fresh(), $variant->fresh()));
    }

    public function test_admin_can_record_a_stock_movement_from_the_back_office(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock_quantity' => 4, 'reserved_quantity' => 0]);

        $this->actingAs($admin)
            ->post(route('admin.stock.store', $product), [
                'type' => StockMovementType::Purchase->value,
                'quantity' => 6,
                'reason' => 'Réapprovisionnement',
            ])
            ->assertRedirect(route('admin.stock.show', $product));

        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'quantity' => 6]);
    }

    public function test_customer_cannot_open_stock_admin(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.stock.index'))
            ->assertForbidden();
    }

    public function test_adjust_stock_sets_absolute_quantity(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 8, 'reserved_quantity' => 0]);

        app(StockService::class)->adjustStock($product, 3, null, 'Inventaire');

        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovementType::Adjustment->value,
            'quantity' => 5,
        ]);
    }
}
