@extends('layouts.storefront')

@section('title', 'Mes favoris — '.shop_name())

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
        <x-storefront.account-nav />
        <h1 class="text-2xl font-semibold tracking-tight text-night-950">Mes favoris</h1>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-storefront.product-card :product="$product" />
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-night-900/15 px-6 py-12 text-center text-sm text-night-800/60">
                    Aucun favori pour le moment.
                </p>
            @endforelse
        </div>
        @if ($products->hasPages())
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
