@extends('layouts.admin')

@section('title', 'Commandes — '.shop_name())
@section('heading', 'Commandes')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Commandes</h1>
            <p class="mt-0.5 text-sm text-slate-500">{{ $orders->total() }} commande{{ $orders->total() > 1 ? 's' : '' }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50/80 p-3">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="N°, client ou téléphone" class="h-9 min-w-[180px] flex-1 rounded-md border border-slate-200 px-3 text-sm">
            <select name="status" class="h-9 rounded-md border border-slate-200 px-2 text-sm">
                <option value="">Tous les statuts</option>
                @foreach (\App\Enums\OrderStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button class="admin-btn h-9 bg-night-950 px-3 text-white">Filtrer</button>
        </form>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Client</th>
                        <th>Total</th>
                        <th>Paiement</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="font-medium">{{ $order->order_number }}</td>
                            <td>
                                <p>{{ $order->customer_name }}</p>
                                <p class="text-xs text-slate-500">{{ $order->customer_phone }}</p>
                            </td>
                            <td>{{ format_price($order->total) }}</td>
                            <td>{{ $order->payment_status->label() }}</td>
                            <td>{{ $order->status->label() }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn admin-btn--ghost">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">Aucune commande pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
