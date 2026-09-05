@extends('layouts.storefront')

@section('title', 'Commande confirmée — '.shop_name())

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <div class="bg-white px-6 py-10 text-center shadow-sm sm:px-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-shop-orange">Merci</p>
            <h1 class="mt-2 text-2xl font-extrabold text-night-950">Commande {{ $order->order_number }}</h1>
            <p class="mt-3 text-sm text-slate-600">Nous vous contacterons au {{ $order->customer_phone }} pour confirmer la livraison. {{ $order->payments->first()?->metadata['customer_message'] ?? $order->payment_method?->label() }} : {{ format_price($order->total) }}.</p>
        </div>
        <div class="mt-6 bg-white p-6 shadow-sm">
            <h2 class="font-bold">Récapitulatif</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-3">
                        <span>{{ $item->quantity }} × {{ $item->product_name }}{{ $item->variant_name ? ' ('.$item->variant_name.')' : '' }}</span>
                        <span>{{ format_price($item->subtotal) }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 flex justify-between border-t border-slate-200 pt-3 font-bold">
                <span>Total</span>
                <span class="text-shop-orange">{{ format_price($order->total) }}</span>
            </p>
            <p class="mt-4 text-sm text-slate-500">{{ $order->delivery_address }}, {{ $order->delivery_commune }} {{ $order->delivery_city }}</p>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('catalog.index') }}" class="rounded bg-shop-green px-4 py-2.5 text-sm font-bold text-white">Continuer vos achats</a>
            @auth
                <a href="{{ route('account.orders.show', $order) }}" class="rounded border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold">Voir mes commandes</a>
            @endauth
            <x-storefront.whatsapp-order-button
                label="Confirmer sur WhatsApp"
                :message="'Bonjour, je viens de passer la commande '.$order->order_number.' ('.format_price($order->total).') chez '.shop_name().'.'"
            />
        </div>
    </section>
@endsection
