@extends('layouts.admin')
@section('title', 'Clients — '.shop_name())
@section('heading', 'Clients')
@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-semibold">Clients</h1>
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom, e-mail, téléphone" class="h-9 rounded-md border border-slate-200 px-3 text-sm">
            <select name="status" class="h-9 rounded-md border border-slate-200 px-2 text-sm">
                <option value="">Tous</option>
                @foreach (\App\Enums\Status::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button class="admin-btn h-9 bg-night-950 px-3 text-white">Filtrer</button>
        </form>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="admin-table">
            <thead><tr><th>Client</th><th>Téléphone</th><th>Commandes</th><th>Statut</th><th></th></tr></thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $customer->name }}</p>
                            <p class="text-xs text-slate-500">{{ $customer->email }}</p>
                        </td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->orders_count }}</td>
                        <td>{{ $customer->status->label() }}</td>
                        <td class="text-right"><a href="{{ route('admin.customers.show', $customer) }}" class="text-sm underline">Fiche</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-400">Aucun client.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($customers->hasPages())
            <div class="border-t px-4 py-3">{{ $customers->links() }}</div>
        @endif
    </div>
@endsection
