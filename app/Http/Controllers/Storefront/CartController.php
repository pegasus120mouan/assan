<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddToCartRequest;
use App\Http\Requests\Storefront\ApplyCouponRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Storefront\CartService;
use App\Services\Storefront\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly CouponService $coupons,
    ) {}

    public function index(): View
    {
        $cart = $this->carts->find();

        return view('storefront.cart.index', [
            'cart' => $cart,
            'totals' => $this->carts->summary($cart),
            'coupon' => $this->coupons->current(),
        ]);
    }

    public function add(AddToCartRequest $request): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));
        $variant = $request->filled('product_variant_id')
            ? ProductVariant::query()->find($request->integer('product_variant_id'))
            : null;

        $this->carts->add($product, $request->integer('quantity'), $variant);

        if ($request->input('intent') === 'buy') {
            return redirect()->route('checkout.show');
        }

        return back()->with('status', 'Produit ajouté au panier.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse
    {
        $this->carts->update($item, $request->integer('quantity'));

        return back()->with('status', 'Panier mis à jour.');
    }

    public function remove(CartItem $item): RedirectResponse
    {
        $this->carts->remove($item);

        return back()->with('status', 'Article retiré du panier.');
    }

    public function clear(): RedirectResponse
    {
        $this->carts->clear();

        return redirect()->route('cart.index')->with('status', 'Panier vidé.');
    }

    public function applyCoupon(ApplyCouponRequest $request): RedirectResponse
    {
        $cart = $this->carts->find();
        $subtotal = $cart?->subtotal() ?? 0;

        $this->coupons->apply($request->validated('code'), $subtotal, $request->user());

        return back()->with('status', 'Code promo appliqué.');
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->coupons->forget();

        return back()->with('status', 'Code promo retiré.');
    }
}
