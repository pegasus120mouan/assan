@extends('layouts.storefront')

@section('title', 'Panier — '.shop_name())

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-night-950">Panier</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $totals['count'] }} article{{ $totals['count'] > 1 ? 's' : '' }}</p>
            </div>
            @if ($cart && ! $cart->isEmpty())
                <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Vider le panier ?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-sm font-medium text-slate-500 hover:text-red-600">Vider le panier</button>
                </form>
            @endif
        </div>

        @if (! $cart || $cart->isEmpty())
            <div class="bg-white px-6 py-16 text-center shadow-sm">
                <p class="text-lg font-semibold">Votre panier est vide.</p>
                <a href="{{ route('catalog.index') }}" class="mt-4 inline-flex rounded bg-shop-green px-5 py-2.5 text-sm font-bold text-white hover:bg-shop-green-dark">Continuer vos achats</a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="overflow-hidden bg-white shadow-sm">
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Produit</th>
                                <th class="px-4 py-3">Prix</th>
                                <th class="px-4 py-3">Qté</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cart->items as $item)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($item->product?->coverUrl())
                                                <img src="{{ $item->product->coverUrl() }}" alt="" width="56" height="56" class="h-14 w-14 rounded object-cover">
                                            @endif
                                            <div>
                                                <a href="{{ $item->product ? route('catalog.product', $item->product) : '#' }}" class="font-medium hover:text-shop-orange">{{ $item->product?->name ?? 'Produit' }}</a>
                                                @if ($item->variant)
                                                    <p class="text-xs text-slate-500">{{ $item->variant->name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4">{{ format_price($item->unit_price) }}</td>
                                    <td class="px-4 py-4">
                                        <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="quantity" min="1" max="99" value="{{ $item->quantity }}" class="w-16 rounded border border-slate-200 px-2 py-1.5 text-sm">
                                            <button class="text-xs font-semibold text-shop-orange">OK</button>
                                        </form>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 font-semibold">{{ format_price($item->subtotal()) }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <form method="POST" action="{{ route('cart.remove', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-xs font-semibold text-red-600">Retirer</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <aside class="h-fit bg-white p-5 shadow-sm">
                    <h2 class="font-bold">Récapitulatif</h2>
                    <form method="POST" action="{{ route('cart.coupon.apply') }}" class="mt-4 flex gap-2">
                        @csrf
                        <input name="code" value="{{ old('code', $coupon?->code) }}" placeholder="Code promo" class="admin-field flex-1">
                        <button class="admin-btn bg-night-950 px-3 text-white">OK</button>
                    </form>
                    @if ($coupon)
                        <form method="POST" action="{{ route('cart.coupon.remove') }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <p class="text-xs text-emerald-700">{{ $coupon->code }} appliqué. <button class="underline">Retirer</button></p>
                        </form>
                    @endif
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt>Sous-total</dt><dd>{{ format_price($totals['subtotal']) }}</dd></div>
                        <div class="flex justify-between text-slate-500"><dt>Réduction</dt><dd>{{ format_price($totals['discount']) }}</dd></div>
                        <div class="flex justify-between text-slate-500"><dt>Livraison</dt><dd>Calculée à la commande</dd></div>
                        <div class="flex justify-between border-t border-slate-200 pt-3 text-base font-bold"><dt>Total</dt><dd class="text-shop-orange">{{ format_price($totals['total']) }}</dd></div>
                    </dl>
                    <a href="{{ route('checkout.show') }}" class="mt-5 inline-flex w-full items-center justify-center rounded bg-shop-green px-4 py-3 text-sm font-bold uppercase tracking-wide text-white hover:bg-shop-green-dark">
                        Commander
                    </a>
                </aside>
            </div>
        @endif
    </section>
@endsection
