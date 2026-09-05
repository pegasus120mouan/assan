@extends('layouts.admin')

@section('title', 'Livraisons — '.shop_name())
@section('heading', 'Livraisons')

@section('content')
    <div class="mb-5"><h1 class="text-xl font-semibold">Livraisons</h1></div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <form method="GET" class="flex flex-wrap gap-2 border-b border-slate-200 bg-slate-50/80 p-3">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Suivi ou commande" class="h-9 min-w-[180px] flex-1 rounded-md border border-slate-200 px-3 text-sm">
            <select name="status" class="h-9 rounded-md border border-slate-200 px-2 text-sm">
                <option value="">Tous</option>
                @foreach (\App\Enums\DeliveryStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button class="admin-btn h-9 bg-night-950 px-3 text-white">Filtrer</button>
        </form>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Suivi</th>
                    <th>Commande</th>
                    <th>Prestataire</th>
                    <th>Frais</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($deliveries as $delivery)
                    <tr>
                        <td class="font-medium">{{ $delivery->tracking_number }}</td>
                        <td>{{ $delivery->order?->order_number }}</td>
                        <td>{{ $delivery->provider->label() }}</td>
                        <td>{{ format_price($delivery->delivery_fee) }}</td>
                        <td>{{ $delivery->status->label() }}</td>
                        <td class="text-right"><a href="{{ route('admin.deliveries.show', $delivery) }}" class="admin-btn admin-btn--ghost">Voir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-400">Aucune livraison.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($deliveries->hasPages())
            <div class="border-t px-4 py-3">{{ $deliveries->links() }}</div>
        @endif
    </div>
@endsection
