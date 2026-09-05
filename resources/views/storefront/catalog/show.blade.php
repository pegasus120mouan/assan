@php
    $whatsapp = shop_whatsapp_number();
    $cover = $product->coverUrl();
    $gallery = $product->images->isNotEmpty() ? $product->images : collect();
    $features = $product->featureLines();
    $variantPayload = $product->variants->map(fn ($variant) => [
        'id' => $variant->id,
        'name' => $variant->name,
        'sku' => $variant->sku,
        'price' => $variant->effectivePrice(),
        'stock' => $variant->availableStock(),
        'formatted_price' => format_price($variant->effectivePrice()),
    ])->values();
@endphp

@extends('layouts.storefront')

@section('title', ($product->meta_title ?: $product->name).' — '.shop_name())
@section('meta_description', $product->meta_description ?: $product->short_description)
@section('og_title', $product->name)
@section('og_description', $product->short_description ?: shop_tagline())
@section('canonical', route('catalog.product', $product))
@section('og_type', 'product')
@if ($cover)
    @section('og_image', str_starts_with((string) $cover, 'http') ? $cover : url($cover))
@endif

@section('content')
    <section
        class="mx-auto max-w-7xl px-4 py-8 sm:px-6"
        x-data="{
            selectedImage: {{ \Illuminate\Support\Js::from($cover) }},
            zoomOpen: false,
            qty: 1,
            productName: {{ \Illuminate\Support\Js::from($product->name) }},
            variants: {{ \Illuminate\Support\Js::from($variantPayload) }},
            selectedId: {{ $product->variants->first()?->id ?? 'null' }},
            get current() {
                return this.variants.find(variant => variant.id == this.selectedId) || null;
            },
            get maxQty() {
                return this.current ? this.current.stock : {{ $product->availableStock() }};
            },
            whatsappLink() {
                const sku = this.current ? this.current.sku : {{ \Illuminate\Support\Js::from($product->sku) }};
                const price = this.current ? this.current.formatted_price : {{ \Illuminate\Support\Js::from(format_price($product->currentPrice())) }};
                const text = [
                    'Bonjour, je souhaite commander :',
                    'Produit : ' + this.productName,
                    'SKU : ' + sku,
                    'Prix : ' + price,
                    'Quantité : ' + this.qty,
                ].join(String.fromCharCode(10));
                return 'https://wa.me/{{ $whatsapp }}?text=' + encodeURIComponent(text);
            }
        }"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <nav class="text-xs text-night-800/50 sm:text-sm">
                <a href="{{ route('home') }}" class="hover:text-night-950">Accueil</a>
                <span> / </span>
                <a href="{{ route('catalog.index') }}" class="hover:text-night-950">Catalogue</a>
                @if ($product->category)
                    @foreach ($product->category->breadcrumb() as $crumb)
                        <span> / </span>
                        <a href="{{ route('catalog.category', $crumb) }}" class="hover:text-night-950">{{ $crumb->name }}</a>
                    @endforeach
                @endif
            </nav>
            <div class="flex items-center gap-2 self-end text-night-800/60">
                @if ($previous)
                    <a href="{{ route('catalog.product', $previous) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-night-900/15 hover:bg-slate-50" aria-label="Produit précédent">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </a>
                @endif
                <a href="{{ $product->category ? route('catalog.category', $product->category) : route('catalog.index') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-night-900/15 hover:bg-slate-50" aria-label="Voir le catalogue">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h4v4H4V6zm6 0h4v4h-4V6zm6 0h4v4h-4V6zM4 14h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4z" /></svg>
                </a>
                @if ($next)
                    <a href="{{ route('catalog.product', $next) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-night-900/15 hover:bg-slate-50" aria-label="Produit suivant">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                @endif
            </div>
        </div>

        <h1 class="mt-4 max-w-5xl text-2xl font-bold leading-tight tracking-tight text-night-950 sm:text-3xl lg:text-4xl">
            {{ $product->name }}
        </h1>

        <div class="mt-8 grid items-start gap-10 lg:grid-cols-2">
            <div>
                <div class="flex gap-3">
                    @if ($gallery->count() > 1)
                        <div class="hidden w-16 shrink-0 flex-col gap-2 sm:flex">
                            @foreach ($gallery as $image)
                                <button type="button" class="aspect-square overflow-hidden rounded-md border-2 bg-slate-50"
                                    :class="selectedImage === '{{ $image->url() }}' ? 'border-shop-green' : 'border-transparent'"
                                    @click="selectedImage = '{{ $image->url() }}'">
                                    <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="relative min-w-0 flex-1 overflow-hidden rounded-xl bg-slate-50">
                        <div class="aspect-square">
                            @if ($cover)
                                <img :src="selectedImage || '{{ $cover }}'" alt="{{ $product->name }}" class="h-full w-full object-contain p-4">
                            @else
                                <span class="flex h-full items-center justify-center text-sm text-night-800/30">{{ shop_name() }}</span>
                            @endif
                        </div>
                        @if ($product->isOnSale())
                            <span class="absolute right-4 top-4 flex h-12 w-12 items-center justify-center rounded-full bg-shop-orange text-xs font-bold text-white shadow">-{{ $product->discountPercent() }}%</span>
                        @endif
                        @if ($cover)
                            <button type="button" class="absolute bottom-4 left-4 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-night-800 shadow" @click="zoomOpen = true" aria-label="Agrandir l'image">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16zM11 8v6M8 11h6" /></svg>
                            </button>
                        @endif
                    </div>
                </div>
                @if ($gallery->count() > 1)
                    <div class="mt-3 flex gap-2 sm:hidden">
                        @foreach ($gallery as $image)
                            <button type="button" class="h-16 w-16 overflow-hidden rounded-md border-2"
                                :class="selectedImage === '{{ $image->url() }}' ? 'border-shop-green' : 'border-night-900/10'"
                                @click="selectedImage = '{{ $image->url() }}'">
                                <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h2 class="text-xl font-bold leading-snug text-night-950 sm:text-2xl">{{ $product->name }}</h2>
                <p class="mt-2 text-sm text-night-800/50">UGS : <span x-text="current ? current.sku : '{{ $product->sku }}'">{{ $product->sku }}</span></p>

                @if ($features->isNotEmpty())
                    <ul class="mt-5 space-y-2 text-sm leading-relaxed text-night-800/80">
                        @foreach ($features as $feature)
                            <li>
                                @if ($feature['label'])
                                    <span class="font-semibold text-night-950">{{ $feature['label'] }}</span>
                                    : {{ $feature['text'] }}
                                @else
                                    {{ $feature['text'] }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($product->description)
                    <p class="mt-5 text-sm leading-relaxed text-night-800/75">{{ $product->description }}</p>
                @endif

                @if ($product->variants->isNotEmpty())
                    <div class="mt-6">
                        <p class="mb-2 text-sm font-semibold">Variante</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($product->variants as $variant)
                                <button type="button" @click="selectedId = {{ $variant->id }}; qty = 1"
                                    class="rounded-md border px-3 py-1.5 text-sm"
                                    :class="selectedId == {{ $variant->id }} ? 'border-night-950 bg-night-950 text-white' : 'border-night-900/15'">
                                    {{ $variant->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap items-end gap-3">
                    @if ($product->isOnSale() && $product->displayComparePrice())
                        <p class="text-lg text-night-800/40 line-through">{{ format_price($product->displayComparePrice()) }}</p>
                    @endif
                    <p class="text-3xl font-extrabold text-shop-orange" x-text="current ? current.formatted_price : '{{ format_price($product->currentPrice()) }}'">{{ format_price($product->currentPrice()) }}</p>
                </div>

                <p class="mt-2 text-sm">
                    <span x-show="maxQty > 0" class="text-emerald-700">En stock</span>
                    <span x-show="maxQty <= 0" class="text-red-600" x-cloak>Rupture de stock</span>
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-stretch overflow-hidden rounded-md border border-night-900/20">
                        <button type="button" class="px-3 py-2 text-lg leading-none hover:bg-slate-50" @click="qty = Math.max(1, qty - 1)" aria-label="Diminuer">−</button>
                        <span class="min-w-[2.5rem] border-x border-night-900/20 px-3 py-2 text-center text-sm font-semibold" x-text="qty">1</span>
                        <button type="button" class="px-3 py-2 text-lg leading-none hover:bg-slate-50" @click="qty = Math.min(Math.max(1, maxQty), qty + 1)" aria-label="Augmenter">+</button>
                    </div>
                </div>

                <div class="mt-4 flex max-w-md flex-col gap-3">
                    <form method="POST" action="{{ route('cart.add') }}" class="contents">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="product_variant_id" value="{{ $product->variants->first()?->id }}" :value="selectedId || ''">
                        <input type="hidden" name="quantity" value="1" :value="qty">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-shop-green px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white hover:bg-shop-green-dark" :disabled="maxQty <= 0">
                            Ajouter Au Panier
                        </button>
                        <button type="submit" name="intent" value="buy" class="inline-flex w-full items-center justify-center rounded-md bg-shop-orange px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white hover:bg-shop-orange-dark" :disabled="maxQty <= 0">
                            Achetez
                        </button>
                    </form>
                    @if ($whatsapp)
                        <a :href="whatsappLink()" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-md border border-[#25D366] bg-white px-6 py-3 text-sm font-semibold uppercase tracking-wide text-[#128C7E] hover:bg-[#25D366]/10">
                            Commander sur WhatsApp
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <section class="mt-14 border-t border-night-900/10 pt-10">
            <h2 class="text-xl font-semibold tracking-tight">Avis</h2>
            @if ($product->reviews_count)
                <p class="mt-1 text-sm text-night-800/60">{{ number_format((float) $product->reviews_avg_rating, 1) }}/5 · {{ $product->reviews_count }} avis</p>
            @endif

            @auth
                @if ($canReview)
                    <form method="POST" action="{{ route('reviews.store', $product) }}" class="mt-6 space-y-3 rounded-2xl border border-night-900/10 p-4">
                        @csrf
                        <p class="text-sm font-medium">Donner votre avis</p>
                        <select name="rating" class="admin-field max-w-[8rem]" required>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected(old('rating', 5) == $i)>{{ $i }}/5</option>
                            @endfor
                        </select>
                        <input name="title" value="{{ old('title') }}" class="admin-field" placeholder="Titre (optionnel)">
                        <textarea name="comment" rows="3" required class="admin-field" placeholder="Votre commentaire">{{ old('comment') }}</textarea>
                        <button class="rounded bg-shop-green px-4 py-2 text-sm font-semibold text-white">Envoyer</button>
                    </form>
                @elseif ($existingReview)
                    <p class="mt-4 text-sm text-slate-500">Votre avis est {{ $existingReview->status->label() }}.</p>
                @endif
            @else
                <p class="mt-4 text-sm text-slate-500"><a href="{{ route('login') }}" class="font-semibold text-shop-orange">Connectez-vous</a> pour laisser un avis après achat.</p>
            @endauth

            <div class="mt-6 space-y-4">
                @forelse ($product->reviews as $review)
                    <article class="rounded-2xl border border-night-900/10 p-4">
                        <p class="text-sm font-medium">{{ $review->user?->name }} · {{ $review->rating }}/5</p>
                        @if ($review->title)
                            <p class="mt-1 text-sm">{{ $review->title }}</p>
                        @endif
                        <p class="mt-1 text-sm text-night-800/70">{{ $review->comment }}</p>
                    </article>
                @empty
                    <p class="text-sm text-night-800/50">Pas encore d’avis sur ce produit.</p>
                @endforelse
            </div>
        </section>

        @if ($similar->isNotEmpty())
            <section class="mt-12">
                <h2 class="text-xl font-semibold tracking-tight">Produits similaires</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similar as $item)
                        <x-storefront.product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif

        <div
            x-cloak
            x-show="zoomOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-night-950/80 p-4"
            @click.self="zoomOpen = false"
            @keydown.escape.window="zoomOpen = false"
        >
            <button type="button" class="absolute right-5 top-5 text-white" @click="zoomOpen = false" aria-label="Fermer">✕</button>
            <img :src="selectedImage" alt="{{ $product->name }}" class="max-h-[90vh] max-w-full object-contain">
        </div>
    </section>
@endsection
