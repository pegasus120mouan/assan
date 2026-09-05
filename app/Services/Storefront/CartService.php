<?php

namespace App\Services\Storefront;

use App\Enums\CartStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    private ?Cart $context = null;

    public function __construct(
        private readonly StockService $stock,
        private readonly CouponService $coupons,
    ) {}

    public function using(Cart $cart): static
    {
        $this->context = $cart;

        return $this;
    }

    public function find(): ?Cart
    {
        if ($this->context?->status === CartStatus::Active) {
            return $this->reload($this->context);
        }

        $query = Cart::query()->active()->with(['items.product.primaryImage', 'items.variant']);

        if ($user = auth()->user()) {
            return $query->where('user_id', $user->id)->latest('id')->first();
        }

        $cartId = session('cart_id');

        if (! $cartId) {
            return null;
        }

        return $query->whereKey($cartId)->whereNull('user_id')->first();
    }

    public function current(): Cart
    {
        $cart = $this->find();

        if ($cart) {
            return $cart;
        }

        $cart = Cart::query()->create([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'status' => CartStatus::Active,
            'last_activity_at' => now(),
        ])->load(['items.product.primaryImage', 'items.variant']);

        if (! auth()->check()) {
            session(['cart_id' => $cart->id]);
        }

        return $cart;
    }

    public function findFromRequest(Request $request): ?Cart
    {
        if ($request->user()) {
            return Cart::query()
                ->active()
                ->where('user_id', $request->user()->id)
                ->with(['items.product.primaryImage', 'items.product.variants', 'items.variant'])
                ->latest('id')
                ->first();
        }

        $token = $request->header('X-Cart-Token');

        if (! $token) {
            return $this->find();
        }

        return Cart::query()
            ->active()
            ->where('session_id', $token)
            ->whereNull('user_id')
            ->with(['items.product.primaryImage', 'items.product.variants', 'items.variant'])
            ->first();
    }

    public function currentFromRequest(Request $request): Cart
    {
        $cart = $this->findFromRequest($request);

        if ($cart) {
            return $this->context = $cart;
        }

        $token = $request->header('X-Cart-Token') ?: (string) Str::uuid();

        $cart = Cart::query()->create([
            'user_id' => $request->user()?->id,
            'session_id' => $token,
            'status' => CartStatus::Active,
            'last_activity_at' => now(),
        ])->load(['items.product.primaryImage', 'items.product.variants', 'items.variant']);

        return $this->context = $cart;
    }

    public function guestToken(?Cart $cart): ?string
    {
        if (! $cart || $cart->user_id) {
            return null;
        }

        return $cart->session_id;
    }

    public function mergeGuestCartByToken(User $user, ?string $token): void
    {
        if (! $token) {
            return;
        }

        $guest = Cart::query()
            ->active()
            ->where('session_id', $token)
            ->whereNull('user_id')
            ->first();

        if (! $guest) {
            return;
        }

        $this->mergeGuestCartIntoUser($user, $guest->id);
    }

    /**
     * @return array{count: int, subtotal: int, discount: int, delivery: int, total: int}
     */
    public function summary(?Cart $cart = null): array
    {
        $cart ??= $this->find();
        $subtotal = $cart?->subtotal() ?? 0;
        $discount = $this->coupons->discountFor($subtotal, auth()->user());
        $delivery = 0;

        return [
            'count' => $cart ? (int) $cart->items->sum('quantity') : 0,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery' => $delivery,
            'total' => max(0, $subtotal - $discount + $delivery),
        ];
    }

    public function add(Product $product, int $quantity, ?ProductVariant $variant = null): CartItem
    {
        $product->loadMissing('variants');

        if (! $product->isActive()) {
            throw ValidationException::withMessages([
                'product_id' => 'Ce produit n’est plus disponible.',
            ]);
        }

        if ($product->variants->isNotEmpty() && $variant === null) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Choisissez une variante.',
            ]);
        }

        if ($variant && $variant->product_id !== $product->id) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Cette variante ne correspond pas au produit.',
            ]);
        }

        return DB::transaction(function () use ($product, $quantity, $variant): CartItem {
            $cart = $this->current();
            $this->stock->assertAvailable($product, $this->quantityAfterAdd($cart, $product, $variant, $quantity), $variant);

            $itemQuery = $cart->items()->where('product_id', $product->id);

            if ($variant) {
                $itemQuery->where('product_variant_id', $variant->id);
            } else {
                $itemQuery->whereNull('product_variant_id');
            }

            $item = $itemQuery->lockForUpdate()->first();
            $unitPrice = $variant?->effectivePrice() ?? $product->currentPrice();

            if ($item) {
                $item->update([
                    'quantity' => $item->quantity + $quantity,
                    'unit_price' => $unitPrice,
                ]);
            } else {
                $item = $cart->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);
            }

            $cart->update(['last_activity_at' => now()]);

            return $item->refresh();
        });
    }

    public function update(CartItem $item, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($item);

            return;
        }

        $item->loadMissing(['product.variants', 'variant', 'cart']);
        $this->assertOwned($item);

        $this->stock->assertAvailable($item->product, $quantity, $item->variant);

        $item->update([
            'quantity' => $quantity,
            'unit_price' => $item->variant?->effectivePrice() ?? $item->product->currentPrice(),
        ]);
        $item->cart->update(['last_activity_at' => now()]);
    }

    public function remove(CartItem $item): void
    {
        $item->loadMissing('cart');
        $this->assertOwned($item);
        $item->delete();
        $item->cart->update(['last_activity_at' => now()]);
    }

    public function clear(?Cart $cart = null): void
    {
        $cart ??= $this->find();

        if (! $cart) {
            return;
        }

        $cart->items()->delete();
        $cart->update(['last_activity_at' => now()]);
    }

    public function mergeGuestCartIntoUser(User $user, ?int $guestCartId = null): void
    {
        if (auth()->id() !== $user->id) {
            throw new \LogicException('Connectez l’utilisateur avant de fusionner le panier.');
        }

        $guestCartId ??= session('cart_id');

        if (! $guestCartId) {
            return;
        }

        $guest = Cart::query()
            ->active()
            ->whereKey($guestCartId)
            ->whereNull('user_id')
            ->with(['items.product.variants', 'items.variant'])
            ->first();

        session()->forget('cart_id');

        if (! $guest || $guest->items->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($guest): void {
            foreach ($guest->items as $item) {
                if (! $item->product || ! $item->product->isActive()) {
                    continue;
                }

                try {
                    $this->add($item->product, $item->quantity, $item->variant);
                } catch (InsufficientStockException|ValidationException) {
                    continue;
                }
            }

            $guest->items()->delete();
            $guest->delete();
        });
    }

    public function markConverted(Cart $cart): void
    {
        $cart->update([
            'status' => CartStatus::Converted,
            'converted_at' => now(),
        ]);

        if ($this->context?->is($cart)) {
            $this->context = null;
        }

        if ((int) session('cart_id') === $cart->id) {
            session()->forget('cart_id');
        }
    }

    private function reload(Cart $cart): Cart
    {
        return $cart->loadMissing(['items.product.primaryImage', 'items.product.variants', 'items.variant']);
    }

    public function assertOwned(CartItem $item): void
    {
        $cart = $this->find();

        if (! $cart || $item->cart_id !== $cart->id) {
            abort(404);
        }
    }

    private function quantityAfterAdd(Cart $cart, Product $product, ?ProductVariant $variant, int $quantity): int
    {
        $existing = $cart->items
            ->first(function (CartItem $item) use ($product, $variant): bool {
                if ($item->product_id !== $product->id) {
                    return false;
                }

                return $variant
                    ? (int) $item->product_variant_id === $variant->id
                    : $item->product_variant_id === null;
            });

        return $quantity + (int) ($existing?->quantity ?? 0);
    }
}
