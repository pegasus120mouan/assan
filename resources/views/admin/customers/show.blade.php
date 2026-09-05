@extends('layouts.admin')
@section('title', $customer->name.' — '.shop_name())
@section('heading', 'Client')
@section('content')
    <p class="mb-4 text-sm"><a href="{{ route('admin.customers.index') }}" class="underline">← Clients</a></p>
    <div class="grid gap-4 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-1">
            <h1 class="text-lg font-semibold">{{ $customer->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $customer->email }}</p>
            <p class="text-sm text-slate-500">{{ $customer->phone }}</p>
            <p class="mt-3 text-sm">{{ $customer->profile?->address }} {{ $customer->profile?->commune }} {{ $customer->profile?->city }}</p>
            <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="mt-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $customer->isActive() ? 'inactive' : 'active' }}">
                <button class="rounded-full border border-slate-200 px-4 py-2 text-sm">
                    {{ $customer->isActive() ? 'Désactiver' : 'Activer' }}
                </button>
            </form>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-2">
            <h2 class="font-semibold">Commandes</h2>
            <table class="admin-table mt-3">
                <thead><tr><th>N°</th><th>Total</th><th>Statut</th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="underline">{{ $order->order_number }}</a></td>
                            <td>{{ format_price($order->total) }}</td>
                            <td>{{ $order->status->label() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-slate-400">Aucune commande.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($orders->hasPages())
                <div class="mt-3">{{ $orders->links() }}</div>
            @endif
        </article>
    </div>
@endsection
