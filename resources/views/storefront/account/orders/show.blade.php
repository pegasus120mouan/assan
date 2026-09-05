@extends('layouts.storefront')

@section('title', $order->order_number.' — '.shop_name())

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <x-storefront.account-nav />
        <p class="text-sm"><a href="{{ route('account.orders.index') }}" class="text-slate-500 hover:text-shop-orange">← Mes commandes</a></p>
        <h1 class="mt-3 text-2xl font-extrabold text-night-950">{{ $order->order_number }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $order->status->label() }} · {{ $order->payment_status->label() }}</p>
        <div class="mt-6 bg-white p-6 shadow-sm">
            <ul class="space-y-2 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-3">
                        <span>{{ $item->quantity }} × {{ $item->product_name }}</span>
                        <span>{{ format_price($item->subtotal) }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 flex justify-between border-t border-slate-200 pt-3 font-bold">
                <span>Total</span>
                <span class="text-shop-orange">{{ format_price($order->total) }}</span>
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('account.orders.invoice', $order) }}" class="inline-flex rounded-full bg-night-950 px-4 py-2 text-sm font-semibold text-white">Voir la facture</a>
                <x-storefront.whatsapp-order-button
                    class="rounded-full"
                    label="Commander / suivre sur WhatsApp"
                    :message="'Bonjour, je souhaite des infos sur ma commande '.$order->order_number.' ('.format_price($order->total).') chez '.shop_name().'.'"
                />
            </div>
        </div>
    </section>
@endsection
