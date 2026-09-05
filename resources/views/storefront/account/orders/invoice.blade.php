@extends('layouts.storefront')

@section('title', 'Facture '.$order->order_number.' — '.shop_name())

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 print:max-w-none print:px-0 print:py-0">
        <div class="mb-6 flex items-center justify-between gap-3 print:hidden">
            <a href="{{ route('account.orders.show', $order) }}" class="text-sm text-slate-500 hover:text-shop-orange">← Commande</a>
            <button type="button" onclick="window.print()" class="rounded-full bg-night-950 px-4 py-2 text-sm font-semibold text-white">Imprimer / PDF</button>
        </div>
        <div class="bg-white p-8 shadow-sm print:shadow-none">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <x-shop-logo variant="compact" />
                    <p class="mt-1 text-sm text-slate-500">{{ config('shop.contact.address') }}</p>
                    <p class="text-sm text-slate-500">{{ config('shop.contact.email') }} · {{ config('shop.contact.phone') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Facture</p>
                    <p class="text-xl font-extrabold">{{ $order->order_number }}</p>
                    <p class="text-sm text-slate-500">{{ $order->created_at->timezone(config('shop.timezone'))->format('d/m/Y') }}</p>
                </div>
            </div>
            <div class="mt-8 text-sm">
                <p class="font-semibold">Facturé à</p>
                <p>{{ $order->customer_name }}</p>
                <p>{{ $order->customer_phone }}</p>
                <p>{{ $order->delivery_address }}, {{ $order->delivery_commune }} {{ $order->delivery_city }}</p>
            </div>
            <table class="mt-8 w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase text-slate-500">
                        <th class="py-2">Article</th>
                        <th class="py-2">SKU</th>
                        <th class="py-2 text-right">Qté</th>
                        <th class="py-2 text-right">Prix</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-slate-100">
                            <td class="py-2">{{ $item->product_name }}</td>
                            <td class="py-2 text-slate-500">{{ $item->sku }}</td>
                            <td class="py-2 text-right">{{ $item->quantity }}</td>
                            <td class="py-2 text-right">{{ format_price($item->unit_price) }}</td>
                            <td class="py-2 text-right">{{ format_price($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-6 ml-auto max-w-xs space-y-1 text-sm">
                <p class="flex justify-between"><span>Sous-total</span><span>{{ format_price($order->subtotal) }}</span></p>
                @if ($order->discount_amount)
                    <p class="flex justify-between"><span>Remise</span><span>-{{ format_price($order->discount_amount) }}</span></p>
                @endif
                <p class="flex justify-between"><span>Livraison</span><span>{{ format_price($order->delivery_fee) }}</span></p>
                <p class="flex justify-between border-t border-slate-200 pt-2 font-bold"><span>Total</span><span>{{ format_price($order->total) }}</span></p>
                <p class="text-xs text-slate-500">{{ $order->payment_method?->label() }} · {{ $order->payment_status->label() }}</p>
            </div>
        </div>
    </section>
@endsection
