<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Cart */
class CartResource extends JsonResource
{
    /**
     * @param  array{count: int, subtotal: int, discount: int, delivery: int, total: int}  $totals
     */
    public function __construct($resource, private readonly array $totals = [], private readonly ?string $cartToken = null)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cart = $this->resource;

        return [
            'cart_token' => $this->cartToken,
            'id' => $cart?->id,
            'items' => $cart?->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'name' => $item->product?->name,
                'sku' => $item->variant?->sku ?? $item->product?->sku,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal(),
                'cover_url' => $item->product?->coverUrl(),
            ])->values() ?? [],
            'count' => $this->totals['count'] ?? 0,
            'subtotal' => $this->totals['subtotal'] ?? 0,
            'discount' => $this->totals['discount'] ?? 0,
            'total' => $this->totals['total'] ?? 0,
            'formatted_total' => format_price($this->totals['total'] ?? 0),
        ];
    }
}
