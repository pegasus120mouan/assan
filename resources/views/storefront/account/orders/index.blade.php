@extends('layouts.storefront')

@section('title', 'Mes commandes — '.shop_name())

@section('content')
    <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
        <x-storefront.account-nav />
        <h1 class="text-2xl font-extrabold text-night-950">Mes commandes</h1>
        <div class="mt-6 overflow-hidden bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">N°</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('account.orders.show', $order) }}" class="hover:text-shop-orange">{{ $order->order_number }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $order->created_at->timezone(config('shop.timezone'))->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ format_price($order->total) }}</td>
                            <td class="px-4 py-3">{{ $order->status->label() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-slate-500">Aucune commande pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="mt-4">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
