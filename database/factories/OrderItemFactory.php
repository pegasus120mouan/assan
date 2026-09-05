<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $unitPrice = fake()->numberBetween(5000, 40000);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null,
            'product_name' => 'Produit',
            'sku' => 'SKU-000000',
            'variant_name' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (OrderItem $item): void {
            if ($item->product) {
                $item->product_name = $item->product->name;
                $item->sku = $item->product->sku;
                $item->unit_price = $item->product->selling_price;
                $item->subtotal = $item->quantity * $item->product->selling_price;
            }
        });
    }
}
