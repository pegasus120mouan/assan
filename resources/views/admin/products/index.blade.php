@extends('layouts.admin')

@section('title', 'Produits — '.shop_name())
@section('heading', 'Produits')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Produits</h1>
            <p class="mt-0.5 text-sm text-slate-500">{{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }} · catalogue, images, variantes, CSV</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.products.export', request()->query()) }}" class="admin-btn admin-btn--ghost h-9 px-3">
                Exporter CSV
            </a>
            <a href="{{ route('admin.products.import') }}" class="admin-btn admin-btn--ghost h-9 px-3">
                Importer
            </a>
            <a href="{{ route('admin.products.create') }}" class="admin-btn h-9 bg-night-950 px-3 text-white hover:bg-night-800">
                Nouveau produit
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50/80 p-3">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom, SKU ou slug"
                class="h-9 min-w-[180px] flex-1 rounded-md border border-slate-200 bg-white px-3 text-sm outline-none focus:border-night-800">
            <select name="category_id" class="h-9 rounded-md border border-slate-200 bg-white px-2 text-sm">
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="brand_id" class="h-9 rounded-md border border-slate-200 bg-white px-2 text-sm">
                <option value="">Toutes les marques</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((string) ($filters['brand_id'] ?? '') === (string) $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
            <select name="status" class="h-9 rounded-md border border-slate-200 bg-white px-2 text-sm">
                <option value="">Tous les statuts</option>
                @foreach (\App\Enums\Status::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <select name="stock" class="h-9 rounded-md border border-slate-200 bg-white px-2 text-sm">
                <option value="">Tous les stocks</option>
                <option value="in" @selected(($filters['stock'] ?? '') === 'in')>En stock</option>
                <option value="out" @selected(($filters['stock'] ?? '') === 'out')>Rupture</option>
                <option value="low" @selected(($filters['stock'] ?? '') === 'low')>Stock bas</option>
            </select>
            <select name="trashed" class="h-9 rounded-md border border-slate-200 bg-white px-2 text-sm">
                <option value="">Catalogue</option>
                <option value="only" @selected(($filters['trashed'] ?? '') === 'only')>Corbeille</option>
                <option value="with" @selected(($filters['trashed'] ?? '') === 'with')>Tout</option>
            </select>
            <label class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 bg-white px-2.5 text-sm">
                <input type="checkbox" name="featured" value="1" @checked(! empty($filters['featured']))>
                Mis en avant
            </label>
            <button type="submit" class="admin-btn h-9 bg-night-950 px-3 text-white">Filtrer</button>
        </form>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <colgroup>
                    <col>
                    <col style="width: 140px">
                    <col style="width: 130px">
                    <col style="width: 110px">
                    <col style="width: 120px">
                    <col style="width: 180px">
                </colgroup>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>SKU</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="{{ $product->trashed() ? 'bg-amber-50/40' : '' }}">
                            <td>
                                <div class="flex min-w-0 items-center gap-3">
                                    @if ($product->coverUrl())
                                        <img src="{{ $product->coverUrl() }}" alt="" width="40" height="40" class="admin-thumb">
                                    @else
                                        <span class="admin-thumb inline-flex items-center justify-center text-[10px] font-semibold uppercase text-slate-400">img</span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-900" title="{{ $product->name }}">{{ $product->name }}</p>
                                        <p class="truncate text-xs text-slate-500">
                                            {{ $product->category?->name ?? 'Sans catégorie' }}{{ $product->brand ? ' · '.$product->brand->name : '' }}
                                        </p>
                                        <div class="mt-0.5 flex flex-wrap gap-1">
                                            @if ($product->featured)
                                                <span class="rounded bg-orange-50 px-1.5 py-0.5 text-[10px] font-semibold text-orange-700">Mis en avant</span>
                                            @endif
                                            @if ($product->is_new)
                                                <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">Nouveau</span>
                                            @endif
                                            @if ($product->isOnSale())
                                                <span class="rounded bg-red-50 px-1.5 py-0.5 text-[10px] font-semibold text-red-700">-{{ $product->discountPercent() }}%</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="font-mono text-xs text-slate-600">{{ $product->sku }}</td>
                            <td class="whitespace-nowrap font-medium">
                                {{ format_price($product->selling_price) }}
                                @if ($product->isOnSale())
                                    <span class="mt-0.5 block text-xs font-normal text-slate-400 line-through">{{ format_price($product->compare_price) }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                @if ($product->isOutOfStock())
                                    <span class="font-semibold text-red-600">0</span>
                                    <span class="block text-[11px] text-red-500">Rupture</span>
                                @elseif ($product->isLowStock())
                                    <span class="font-semibold text-amber-600">{{ $product->availableStock() }}</span>
                                    <span class="block text-[11px] text-amber-600">Stock bas</span>
                                @else
                                    <span class="font-medium text-slate-800">{{ $product->availableStock() }}</span>
                                @endif
                                @if ($product->variants_count)
                                    <span class="block text-[11px] text-slate-400">{{ $product->variants_count }} variante(s)</span>
                                @endif
                            </td>
                            <td>
                                @if ($product->trashed())
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Corbeille</span>
                                @elseif ($product->isActive())
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ $product->status->label() }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $product->status->label() }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-1.5">
                                    @if ($product->trashed())
                                        <form method="POST" action="{{ route('admin.products.restore', $product) }}">
                                            @csrf
                                            <button type="submit" class="admin-btn admin-btn--ghost">Restaurer</button>
                                        </form>
                                        <button
                                            type="button"
                                            class="admin-btn admin-btn--danger"
                                            @click="$dispatch('admin-confirm-delete', {{ \Illuminate\Support\Js::from([
                                                'title' => 'Supprimer définitivement',
                                                'message' => 'Supprimer définitivement « '.$product->name.' » ? Cette action est irréversible.',
                                                'action' => route('admin.products.force-destroy', $product),
                                                'confirmLabel' => 'Supprimer définitivement',
                                            ]) }})"
                                        >Supprimer</button>
                                    @else
                                        <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn admin-btn--ghost">Modifier</a>
                                        <button
                                            type="button"
                                            class="admin-btn admin-btn--danger"
                                            @click="$dispatch('admin-confirm-delete', {{ \Illuminate\Support\Js::from([
                                                'title' => 'Mettre à la corbeille',
                                                'message' => 'Mettre « '.$product->name.' » à la corbeille ?',
                                                'action' => route('admin.products.destroy', $product),
                                                'confirmLabel' => 'Mettre à la corbeille',
                                            ]) }})"
                                        >Supprimer</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400">Aucun produit pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
