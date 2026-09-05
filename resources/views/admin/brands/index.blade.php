@extends('layouts.admin')

@section('title', 'Marques — '.shop_name())
@section('heading', 'Marques')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Marques</h1>
            <p class="mt-1 text-sm text-night-800/70">Gérez les marques du catalogue.</p>
        </div>
        <a href="{{ route('admin.brands.create') }}" class="inline-flex items-center justify-center rounded-full bg-night-950 px-4 py-2 text-sm font-semibold text-white">
            Nouvelle marque
        </a>
    </div>

    <form method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Rechercher une marque"
            class="w-full rounded-xl border border-night-900/15 bg-white px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2 sm:max-w-xs">
        <select name="status" class="rounded-xl border border-night-900/15 bg-white px-3 py-2.5 text-sm">
            <option value="">Tous les statuts</option>
            @foreach (\App\Enums\Status::cases() as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-full border border-night-900/15 bg-white px-4 py-2 text-sm font-medium">Filtrer</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-night-900/10 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-night-800/50">
                    <tr>
                        <th class="px-4 py-3">Marque</th>
                        <th class="px-4 py-3">Produits</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-night-900/10">
                    @forelse ($brands as $brand)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($brand->logoUrl())
                                        <img src="{{ $brand->logoUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                    @else
                                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-xs text-night-800/40">logo</span>
                                    @endif
                                    <div>
                                        <p class="font-medium">{{ $brand->name }}</p>
                                        <p class="text-xs text-night-800/50">{{ $brand->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $brand->products_count }}</td>
                            <td class="px-4 py-3">
                                <span @class(['rounded-full px-2 py-0.5 text-xs', 'bg-emerald-50 text-emerald-700' => $brand->isActive(), 'bg-slate-100 text-night-800/60' => ! $brand->isActive()])>
                                    {{ $brand->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="text-sm underline">Modifier</a>
                                    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Supprimer cette marque ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 underline">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-night-800/50">Aucune marque pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($brands->hasPages())
            <div class="border-t border-night-900/10 px-4 py-3">{{ $brands->links() }}</div>
        @endif
    </div>
@endsection
