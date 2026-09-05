@extends('layouts.admin')

@section('title', 'Catégories — '.shop_name())
@section('heading', 'Catégories')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Catégories</h1>
            <p class="mt-1 text-sm text-night-800/70">Organisez le catalogue de la boutique.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center rounded-full bg-night-950 px-4 py-2 text-sm font-semibold text-white">
            Nouvelle catégorie
        </a>
    </div>

    <form method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Rechercher une catégorie"
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
                        <th class="px-4 py-3">Catégorie</th>
                        <th class="px-4 py-3">Parente</th>
                        <th class="px-4 py-3">Produits</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-night-900/10">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-shop-orange">
                                        <x-category-icon :icon="$category->icon" class="h-5 w-5" />
                                    </span>
                                    @if ($category->imageUrl())
                                        <img src="{{ $category->imageUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                    @endif
                                    <div>
                                        <p class="font-medium">{{ $category->name }}</p>
                                        <p class="text-xs text-night-800/50">{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-night-800/70">{{ $category->parent?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $category->products_count }}</td>
                            <td class="px-4 py-3">
                                <span @class(['rounded-full px-2 py-0.5 text-xs', 'bg-emerald-50 text-emerald-700' => $category->isActive(), 'bg-slate-100 text-night-800/60' => ! $category->isActive()])>
                                    {{ $category->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-sm underline">Modifier</a>
                                    <button
                                        type="button"
                                        class="text-sm text-red-600 underline"
                                        @click="$dispatch('admin-confirm-delete', {{ \Illuminate\Support\Js::from([
                                            'title' => 'Supprimer la catégorie',
                                            'message' => 'Supprimer « '.$category->name.' » ?',
                                            'action' => route('admin.categories.destroy', $category),
                                        ]) }})"
                                    >Supprimer</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-night-800/50">Aucune catégorie pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="border-t border-night-900/10 px-4 py-3">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
