@extends('layouts.storefront')

@section('title', ($term ? 'Recherche : '.$term : 'Recherche').' — '.shop_name())

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-semibold tracking-tight text-night-950">Recherche</h1>
        <form method="GET" action="{{ route('search') }}" class="mt-6">
            <input type="search" name="q" value="{{ $term }}" placeholder="Nom, SKU, catégorie ou marque" class="w-full max-w-xl rounded-md border border-night-900/15 px-5 py-3 text-sm outline-none ring-shop-green focus:ring-2">
        </form>
        <p class="mt-4 text-sm text-night-800/60">{{ $products->total() }} résultat(s){{ $term !== '' ? ' pour « '.$term.' »' : '' }}</p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-storefront.product-card :product="$product" />
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-night-900/15 bg-white px-6 py-14 text-center">
                    <p class="text-lg font-semibold text-night-950">Aucun produit trouvé</p>
                    <p class="mt-2 text-sm text-night-800/60">
                        @if ($term !== '')
                            Aucun article ne correspond à « {{ $term }} ».
                        @else
                            Saisissez un nom, un SKU ou une marque pour lancer une recherche.
                        @endif
                    </p>
                    <a href="{{ route('catalog.index') }}" class="mt-6 inline-flex rounded-md bg-shop-green px-5 py-2.5 text-sm font-semibold text-white hover:bg-shop-green-dark">Voir le catalogue</a>
                </div>
            @endforelse
        </div>
        @if ($products->hasPages())
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
