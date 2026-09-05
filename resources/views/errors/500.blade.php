@extends('layouts.storefront')

@section('title', 'Erreur — '.shop_name())

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
        <p class="text-sm font-bold uppercase tracking-wide text-shop-orange">Oups</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-night-950">Une erreur s’est produite</h1>
        <p class="mt-3 text-sm text-night-800/70">Réessayez dans un instant, ou poursuivez vos achats dans le catalogue.</p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="rounded-md bg-shop-green px-5 py-2.5 text-sm font-semibold text-white hover:bg-shop-green-dark">Retour à l’accueil</a>
            <a href="{{ route('catalog.index') }}" class="rounded-md border border-night-900/15 px-5 py-2.5 text-sm font-semibold text-night-950 hover:bg-white">Voir le catalogue</a>
        </div>
    </section>
@endsection
