@extends('layouts.storefront')

@section('title', ($currentCategory->name ?? 'Catalogue').' — '.shop_name())
@section('meta_description', $currentCategory->description ?? shop_tagline())

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <p class="text-sm text-night-800/50">
            <a href="{{ route('home') }}" class="underline">Accueil</a>
            ·
            <a href="{{ route('catalog.index') }}" class="underline">Catalogue</a>
            @if ($currentCategory)
                · {{ $currentCategory->name }}
            @endif
        </p>
        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-night-950">{{ $currentCategory->name ?? 'Catalogue' }}</h1>
                @if ($currentCategory?->description)
                    <p class="mt-2 max-w-2xl text-sm text-night-800/70">{{ $currentCategory->description }}</p>
                @endif
            </div>
            <p class="text-sm text-night-800/50">{{ $products->total() }} produit(s)</p>
        </div>

        @if ($currentCategory?->children?->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($currentCategory->children as $child)
                    <a href="{{ route('catalog.category', $child) }}" class="rounded-full border border-night-900/15 px-3 py-1.5 text-sm hover:bg-slate-50">{{ $child->name }}</a>
                @endforeach
            </div>
        @endif

        <form method="GET" class="mt-8 grid gap-3 rounded-2xl border border-night-900/10 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
            @if (! $currentCategory)
                <select name="category" class="rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="brand" class="rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
                <option value="">Toutes les marques</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((string) ($filters['brand'] ?? '') === (string) $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
            <input type="number" name="min_price" min="0" value="{{ $filters['min_price'] ?? '' }}" placeholder="Prix min (FCFA)" class="rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
            <input type="number" name="max_price" min="0" value="{{ $filters['max_price'] ?? '' }}" placeholder="Prix max (FCFA)" class="rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
            <select name="sort" class="rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
                <option value="">Pertinence</option>
                <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Prix croissant</option>
                <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Prix décroissant</option>
                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Nouveautés</option>
                <option value="best_sellers" @selected(($filters['sort'] ?? '') === 'best_sellers')>Meilleures ventes</option>
            </select>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="in_stock" value="1" @checked(! empty($filters['in_stock']))> En stock</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="on_sale" value="1" @checked(! empty($filters['on_sale']))> Promotions</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_new" value="1" @checked(! empty($filters['is_new']))> Nouveautés</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="best_seller" value="1" @checked(! empty($filters['best_seller']))> Best sellers</label>
            <button class="rounded-md bg-shop-green px-4 py-2 text-sm font-semibold text-white hover:bg-shop-green-dark sm:col-span-2 lg:col-span-4">Filtrer</button>
        </form>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-storefront.product-card :product="$product" />
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-night-900/15 px-6 py-12 text-center text-sm text-night-800/60">
                    Aucun produit dans cette sélection pour le moment.
                </p>
            @endforelse
        </div>

        @if ($products->hasPages())
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
