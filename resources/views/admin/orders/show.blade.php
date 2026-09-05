@extends('layouts.admin')

@section('title', $order->order_number.' — '.shop_name())
@section('heading', 'Commande')

@section('content')
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ $order->order_number }}</h1>
            <p class="text-sm text-slate-500">{{ $order->created_at->timezone(config('shop.timezone'))->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-slate-500">← Liste</a>
    </div>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="space-y-5">
            <section class="admin-card">
                <h2 class="admin-card__title">Articles</h2>
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-t border-slate-100 first:border-0">
                                <td class="py-2">
                                    <p class="font-medium">{{ $item->product_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->sku }}{{ $item->variant_name ? ' · '.$item->variant_name : '' }}</p>
                                </td>
                                <td class="py-2 text-right">{{ $item->quantity }} × {{ format_price($item->unit_price) }}</td>
                                <td class="py-2 text-right font-medium">{{ format_price($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="mt-4 flex justify-between border-t border-slate-200 pt-3 font-bold">
                    <span>Total</span>
                    <span>{{ format_price($order->total) }}</span>
                </p>
                <p class="mt-1 text-sm text-slate-500">Dont livraison {{ format_price($order->delivery_fee) }}</p>
            </section>
            <section class="admin-card">
                <h2 class="admin-card__title">Livraison</h2>
                <p class="text-sm">{{ $order->customer_name }} · {{ $order->customer_phone }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ $order->delivery_address }}</p>
                <p class="text-sm text-slate-600">{{ $order->delivery_commune }} {{ $order->delivery_city }}</p>
                @if ($order->customer_notes)
                    <p class="mt-3 text-sm text-slate-500">{{ $order->customer_notes }}</p>
                @endif
            </section>
        </div>
        <aside class="space-y-5">
            <section class="admin-card">
                <h2 class="admin-card__title">Statut</h2>
                <p class="text-sm">Paiement : {{ $order->payment_status->label() }}</p>
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="admin-field">
                        @foreach (\App\Enums\OrderStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($order->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <button class="admin-btn h-10 w-full bg-night-950 text-white">Mettre à jour</button>
                </form>
            </section>
        </aside>
    </div>
@endsection
