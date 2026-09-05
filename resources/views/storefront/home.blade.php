@extends('layouts.storefront')

@section('title', shop_name().' — '.shop_tagline())
@section('meta_description', shop_tagline())

@section('content')
    @php
        $slideCount = $heroSlides->count();
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-5 sm:px-6">
        <div class="grid gap-4 lg:grid-cols-12">
            <aside class="hidden overflow-hidden bg-white shadow-sm lg:col-span-3 lg:block">
                <p class="shop-cats-btn w-full justify-between">
                    Catégories
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </p>
                <nav>
                    @forelse ($categories as $category)
                        <a href="{{ route('catalog.category', $category) }}" class="shop-cat-link">
                            <x-category-icon :icon="$category->icon" class="text-shop-green" />
                            <span class="shop-cat-link__name">{{ $category->name }}</span>
                            <span class="shop-cat-link__count">{{ $category->published_products_count ?? 0 }}</span>
                        </a>
                    @empty
                        <a href="{{ route('catalog.index') }}" class="shop-cat-link">
                            <span></span>
                            <span class="shop-cat-link__name">Voir le catalogue</span>
                        </a>
                    @endforelse
                    <a href="{{ route('catalog.index') }}" class="shop-cat-link font-semibold text-shop-green">
                        <x-category-icon icon="grid" />
                        <span class="shop-cat-link__name">Tout le catalogue</span>
                    </a>
                </nav>
            </aside>

            <div
                class="relative lg:col-span-6"
                @if ($slideCount > 1)
                    x-data="{ i: 0, n: {{ $slideCount }} }"
                    x-init="setInterval(() => i = (i + 1) % n, 6000)"
                @endif
            >
                @forelse ($heroSlides as $index => $slide)
                    <article
                        @if ($slideCount > 1)
                            x-show="i === {{ $index }}"
                            x-transition.opacity
                            @if ($index > 0) x-cloak @endif
                        @endif
                        class="shop-hero {{ $slideCount > 1 && $index > 0 ? 'absolute inset-0' : '' }}"
                    >
                        <div class="shop-hero__copy">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-shop-green-light">{{ shop_name() }}</p>
                            <h1 class="mt-3 text-3xl font-extrabold leading-tight sm:text-4xl">{{ $slide->name }}</h1>
                            <p class="mt-3 line-clamp-2 text-sm text-white/75">{{ $slide->short_description ?: shop_tagline() }}</p>
                            <p class="mt-4 text-2xl font-extrabold text-orange-400">{{ format_price($slide->currentPrice()) }}</p>
                            <a href="{{ route('catalog.product', $slide) }}" class="mt-5 inline-flex w-fit rounded bg-shop-green px-5 py-2.5 text-sm font-bold text-white hover:bg-shop-green-dark">
                                Découvrir
                            </a>
                        </div>
                        @if ($slide->coverUrl())
                            <div class="shop-hero__media">
                                <img src="{{ $slide->coverUrl() }}" alt="{{ $slide->name }}">
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="shop-hero">
                        <div class="shop-hero__copy">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-shop-green-light">Abidjan · {{ shop_name() }}</p>
                            <h1 class="mt-3 max-w-xl text-3xl font-extrabold leading-tight sm:text-4xl">{{ shop_tagline() }}</h1>
                            <p class="mt-4 max-w-lg text-sm text-white/75">Gadgets utiles, accessoires smartphone, Smart Home, PC et création de contenu — sélectionnés pour le quotidien en Côte d'Ivoire.</p>
                            <a href="{{ route('catalog.index') }}" class="mt-6 inline-flex w-fit rounded bg-shop-green px-5 py-2.5 text-sm font-bold text-white hover:bg-shop-green-dark">
                                Découvrir
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="flex flex-col gap-4 lg:col-span-3">
                @php
                    $tileA = $promoTiles->get(0);
                    $tileB = $promoTiles->get(1);
                @endphp
                <a href="{{ $tileA ? route('catalog.category', $tileA) : route('catalog.index', ['on_sale' => 1]) }}" @class(['shop-promo shop-promo--navy', 'has-image' => $tileA?->displayImageUrl()])>
                    @if ($tileA?->displayImageUrl())
                        <span class="shop-promo__media">
                            <img src="{{ $tileA->displayImageUrl() }}" alt="{{ $tileA->name }}">
                        </span>
                    @endif
                    <span class="shop-promo__content">
                        <h2 class="text-xl font-extrabold">{{ $tileA->name ?? 'Deals du jour' }}</h2>
                        <span class="mt-3 inline-flex w-fit rounded bg-shop-orange px-3 py-1.5 text-xs font-bold uppercase tracking-wide">Voir Plus</span>
                    </span>
                </a>
                <a href="{{ $tileB ? route('catalog.category', $tileB) : route('catalog.index', ['is_new' => 1]) }}" @class(['shop-promo shop-promo--green', 'has-image' => $tileB?->displayImageUrl()])>
                    @if ($tileB?->displayImageUrl())
                        <span class="shop-promo__media">
                            <img src="{{ $tileB->displayImageUrl() }}" alt="{{ $tileB->name }}">
                        </span>
                    @endif
                    <span class="shop-promo__content">
                        <h2 class="text-xl font-extrabold">{{ $tileB->name ?? 'Nouvel Arrivage' }}</h2>
                        <span class="mt-3 inline-flex w-fit rounded bg-white px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-shop-green">Voir Plus</span>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <section id="catalogue" class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <div class="mb-5 flex items-end justify-between gap-4">
            <h2 class="text-2xl font-extrabold tracking-tight text-night-950">Meilleures catégories</h2>
            <a href="{{ route('catalog.index') }}" class="text-sm font-semibold text-shop-orange hover:underline">Tout voir</a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($bestCategories as $category)
                <x-storefront.category-card :category="$category" />
            @empty
                <p class="col-span-full bg-white px-6 py-10 text-center text-sm text-gray-500">Les catégories apparaîtront ici dès qu’elles seront publiées.</p>
            @endforelse
        </div>
    </section>

    <section id="deals" class="mx-auto max-w-7xl px-4 py-4 sm:px-6">
        <div class="mb-5 flex items-end justify-between gap-4">
            <h2 class="text-2xl font-extrabold tracking-tight text-night-950">{{ $spotlightTitle }}</h2>
            <a href="{{ route('catalog.index', $onSale->isNotEmpty() ? ['on_sale' => 1] : []) }}" class="text-sm font-semibold text-shop-orange hover:underline">Voir Plus</a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($spotlight as $product)
                <x-storefront.product-card :product="$product" />
            @empty
                <p class="col-span-full bg-white px-6 py-10 text-center text-sm text-gray-500">Les produits apparaîtront ici dès leur publication.</p>
            @endforelse
        </div>
    </section>

    @if ($newArrivals->isNotEmpty())
        <section id="nouveautes" class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
            <div class="mb-5 flex items-end justify-between gap-4">
                <h2 class="text-2xl font-extrabold tracking-tight text-night-950">Nouveautés</h2>
                <a href="{{ route('catalog.index', ['is_new' => 1]) }}" class="text-sm font-semibold text-shop-orange hover:underline">Voir Plus</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($newArrivals as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($bestSellers->isNotEmpty())
        <section id="best-sellers" class="mx-auto max-w-7xl px-4 py-4 sm:px-6">
            <div class="mb-5 flex items-end justify-between gap-4">
                <h2 class="text-2xl font-extrabold tracking-tight text-night-950">Best sellers</h2>
                <a href="{{ route('catalog.index', ['best_seller' => 1]) }}" class="text-sm font-semibold text-shop-orange hover:underline">Voir Plus</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($bestSellers as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @else
        <div id="best-sellers"></div>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['title' => 'Livraison Abidjan', 'text' => 'Expédition rapide dans toute la Côte d’Ivoire'],
                ['title' => 'Paiement à la livraison', 'text' => 'Payez une fois le produit reçu'],
                ['title' => 'Service WhatsApp', 'text' => 'Commandez et posez vos questions en direct'],
                ['title' => 'Tech sélectionnée', 'text' => 'Gadgets et accessoires utiles au quotidien'],
            ] as $perk)
                <article class="bg-white px-5 py-4 shadow-sm">
                    <p class="font-bold text-night-950">{{ $perk['title'] }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $perk['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-10 sm:px-6">
        <div class="bg-white px-6 py-10 sm:px-10">
            <h2 class="text-2xl font-extrabold text-night-950">Bienvenue sur {{ shop_name() }}</h2>
            <p class="mt-4 max-w-4xl text-sm leading-relaxed text-gray-600">
                {{ shop_name() }} est votre boutique tech à Abidjan. Gadgets utiles, accessoires smartphone, Smart Home, PC et création de contenu — sélectionnés pour le quotidien en Côte d’Ivoire.
                Vos commandes sont livrées dans les plus brefs délais à Abidjan et partout en Côte d’Ivoire.
            </p>
            <p class="mt-3 text-sm font-semibold text-night-950">{{ shop_tagline() }}</p>
        </div>
    </section>

    <section class="bg-shop-green py-10 text-white">
        <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 px-4 sm:flex-row sm:items-center sm:px-6">
            <div>
                <h2 class="text-2xl font-extrabold">Restez Informé !</h2>
                <p class="mt-1 text-sm text-white/90">Inscrivez-vous pour recevoir nos meilleures offres.</p>
            </div>
            <form class="flex w-full max-w-md overflow-hidden rounded bg-white" action="mailto:{{ config('shop.contact.email') }}" method="GET">
                <input type="email" name="body" required placeholder="Votre e-mail" class="min-w-0 flex-1 px-4 py-3 text-sm text-night-950 outline-none">
                <button type="submit" class="bg-night-950 px-5 text-sm font-bold text-white">S'inscrire</button>
            </form>
        </div>
    </section>
@endsection
