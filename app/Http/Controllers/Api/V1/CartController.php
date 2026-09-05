<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddToCartRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Http\Resources\Api\V1\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Storefront\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function show(Request $request): CartResource
    {
        $cart = $this->carts->findFromRequest($request);

        return $this->payload($request, $cart);
    }

    public function add(AddToCartRequest $request): CartResource
    {
        $cart = $this->carts->currentFromRequest($request);
        $product = Product::query()->findOrFail($request->integer('product_id'));
        $variant = $request->filled('product_variant_id')
            ? ProductVariant::query()->find($request->integer('product_variant_id'))
            : null;

        $this->carts->using($cart)->add($product, $request->integer('quantity'), $variant);

        return $this->payload($request, $cart->fresh(['items.product.primaryImage', 'items.variant']));
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): CartResource
    {
        $cart = $this->carts->currentFromRequest($request);
        $this->carts->using($cart)->update($item, $request->integer('quantity'));

        return $this->payload($request, $cart->fresh(['items.product.primaryImage', 'items.variant']));
    }

    public function remove(Request $request, CartItem $item): CartResource
    {
        $cart = $this->carts->currentFromRequest($request);
        $this->carts->using($cart)->remove($item);

        return $this->payload($request, $cart->fresh(['items.product.primaryImage', 'items.variant']));
    }

    public function clear(Request $request): CartResource
    {
        $cart = $this->carts->findFromRequest($request);
        $this->carts->clear($cart);

        return $this->payload($request, $cart?->fresh(['items.product.primaryImage', 'items.variant']));
    }

    private function payload(Request $request, ?Cart $cart): CartResource
    {
        return new CartResource(
            $cart,
            $this->carts->summary($cart),
            $this->carts->guestToken($cart) ?? $request->header('X-Cart-Token'),
        );
    }
}
