@php
    $rating = (float) ($product->reviews_avg_rating ?? 0);
@endphp

<article class="group relative flex flex-col border border-gray-100 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="absolute left-5 top-5 z-10 flex flex-col gap-1">
        @if ($product->isOnSale())
            <span class="inline-flex w-fit bg-shop-orange px-2 py-0.5 text-xs font-bold text-white">-{{ $product->discountPercent() }}%</span>
        @endif
        @if ($product->featured && $product->isOnSale())
            <span class="inline-flex w-fit bg-night-950 px-2 py-0.5 text-xs font-semibold text-white">Top Deal</span>
        @elseif ($product->is_new)
            <span class="inline-flex w-fit bg-shop-green px-2 py-0.5 text-xs font-semibold text-white">Nouveau</span>
        @endif
    </div>
    @php $saved = in_array($product->id, $wishlistProductIds ?? [], true); @endphp
    @auth
        <form method="POST" action="{{ route('wishlist.toggle', $product) }}">
            @csrf
            <button type="submit" class="absolute right-5 top-5 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 shadow-sm {{ $saved ? 'text-shop-orange' : 'text-night-800/40 hover:text-shop-orange' }}" title="{{ $saved ? 'Retirer des favoris' : 'Ajouter aux favoris' }}" aria-label="{{ $saved ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true" @if ($saved) fill="currentColor" @else fill="none" stroke="currentColor" @endif>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="absolute right-5 top-5 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-night-800/40 shadow-sm hover:text-shop-orange" title="Connectez-vous pour ajouter aux favoris" aria-label="Ajouter aux favoris">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </a>
    @endauth

    <a href="{{ route('catalog.product', $product) }}" class="relative aspect-square overflow-hidden bg-slate-50">
        @if ($product->coverUrl())
            <img src="{{ $product->coverUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-contain p-3 transition group-hover:scale-[1.03]">
        @else
            <span class="flex h-full items-center justify-center text-sm text-night-800/30">{{ shop_name() }}</span>
        @endif
        @if ($product->isOutOfStock())
            <span class="absolute bottom-3 left-3 rounded bg-night-950/80 px-2 py-0.5 text-xs text-white">Rupture</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col pt-3">
        <h3 class="line-clamp-2 min-h-[2.5rem] text-sm font-medium leading-snug text-night-950">
            <a href="{{ route('catalog.product', $product) }}" class="hover:text-shop-orange">{{ $product->name }}</a>
        </h3>

        <div class="mt-2 flex items-center gap-1" aria-label="Note {{ number_format($rating, 2) }} sur 5">
            @for ($i = 1; $i <= 5; $i++)
                <svg class="h-3.5 w-3.5 {{ $rating >= $i ? 'text-amber-400' : 'text-night-900/15' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @endfor
            <span class="ml-1 text-[11px] text-night-800/50">{{ number_format($rating, 2) }}</span>
        </div>

        <div class="mt-2 flex flex-wrap items-baseline gap-2">
            @if ($product->isOnSale() && $product->displayComparePrice())
                <span class="text-xs text-night-800/40 line-through">{{ format_price($product->displayComparePrice()) }}</span>
            @endif
            <span class="text-base font-bold text-shop-orange">{{ format_price($product->currentPrice()) }}</span>
        </div>

        @if ($product->isOutOfStock())
            <span class="mt-3 inline-flex items-center justify-center rounded-md bg-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Rupture</span>
        @elseif ($product->relationLoaded('variants') && $product->variants->isNotEmpty())
            <a href="{{ route('catalog.product', $product) }}" class="mt-3 inline-flex items-center justify-center rounded-md bg-shop-green px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-shop-green-dark">
                Ajouter au panier
            </a>
        @else
            <form method="POST" action="{{ route('cart.add') }}" class="mt-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-shop-green px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-shop-green-dark">
                    Ajouter au panier
                </button>
            </form>
        @endif

        <p class="mt-2 text-[11px] text-night-800/40">SKU: {{ $product->sku }}</p>
    </div>
</article>
