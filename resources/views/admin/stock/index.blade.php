@extends('layouts.admin')

@section('title', 'Stocks — '.shop_name())
@section('heading', 'Stocks')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Stocks</h1>
            <p class="mt-1 text-sm text-night-800/70">Mouvements, stock disponible et alertes de seuil.</p>
        </div>
    </div>

    <form method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom ou SKU"
            class="w-full rounded-xl border border-night-900/15 bg-white px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2 sm:max-w-xs">
        <select name="stock" class="rounded-xl border border-night-900/15 bg-white px-3 py-2.5 text-sm">
            <option value="">Tous</option>
            <option value="low" @selected(($filters['stock'] ?? '') === 'low')>Stock bas</option>
            <option value="out" @selected(($filters['stock'] ?? '') === 'out')>Rupture</option>
        </select>
        <button type="submit" class="rounded-full border border-night-900/15 bg-white px-4 py-2 text-sm font-medium">Filtrer</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-night-900/10 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-night-800/50">
                    <tr>
                        <th class="px-4 py-3">Produit</th>
                        <th class="px-4 py-3">Stock</th>
                        <th class="px-4 py-3">Réservé</th>
                        <th class="px-4 py-3">Disponible</th>
                        <th class="px-4 py-3">État</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-night-900/10">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $product->name }}</p>
                                <p class="text-xs text-night-800/50">{{ $product->sku }} · {{ $product->category?->name }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $product->variants->isNotEmpty() ? $product->variants->sum('stock_quantity') : $product->stock_quantity }}</td>
                            <td class="px-4 py-3">{{ $product->variants->isNotEmpty() ? $product->variants->sum('reserved_quantity') : $product->reserved_quantity }}</td>
                            <td class="px-4 py-3 font-medium">{{ $product->availableStock() }}</td>
                            <td class="px-4 py-3">
                                @if ($product->isOutOfStock())
                                    <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs text-red-700">Rupture</span>
                                @elseif ($product->isLowStock())
                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-800">Stock bas</span>
                                @else
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">OK</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.stock.show', $product) }}" class="text-sm underline">Mouvements</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-night-800/50">Aucun produit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="border-t border-night-900/10 px-4 py-3">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
