<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\DeliveryMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Models\Commune;
use App\Models\Order;
use App\Services\Delivery\DeliveryFeeService;
use App\Services\Payments\PaymentService;
use App\Services\Storefront\CartService;
use App\Services\Storefront\CheckoutService;
use App\Services\Storefront\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
        private readonly CouponService $coupons,
        private readonly DeliveryFeeService $fees,
        private readonly PaymentService $payments,
    ) {}

    public function show(): View|RedirectResponse
    {
        $cart = $this->carts->find();

        if (! $cart || $cart->isEmpty()) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Votre panier est vide.',
            ]);
        }

        $method = DeliveryMethod::Standard;
        $communes = Commune::query()->active()->with('city')->orderBy('name')->get();
        $communeQuotes = $communes->mapWithKeys(
            fn (Commune $commune): array => [$commune->id => $this->fees->quoteCart($cart, $commune, $method)]
        );
        $totals = $this->carts->summary($cart);
        $totals['delivery'] = $this->fees->quoteCart($cart, $communes->first(), $method);
        $totals['total'] = max(0, $totals['subtotal'] - $totals['discount'] + $totals['delivery']);

        return view('storefront.checkout.show', [
            'cart' => $cart,
            'totals' => $totals,
            'user' => auth()->user(),
            'coupon' => $this->coupons->current(),
            'communes' => $communes,
            'communeQuotes' => $communeQuotes,
            'gateways' => $this->payments->enabled(),
            'defaultFee' => (int) config('delivery.standard_fee', 2000),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->checkout->place($request->validated());

        $request->session()->put('placed_order_id', $order->id);

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Order $order): View
    {
        $this->authorizeView($order);
        $order->load(['items', 'payments', 'delivery']);

        return view('storefront.checkout.confirmation', [
            'order' => $order,
        ]);
    }

    private function authorizeView(Order $order): void
    {
        $user = auth()->user();

        if ($user?->isStaff() || ($user && $order->user_id === $user->id)) {
            return;
        }

        if ((int) session('placed_order_id') === $order->id) {
            return;
        }

        abort(403);
    }
}
