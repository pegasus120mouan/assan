@extends('layouts.storefront')

@section('title', 'Mon compte — '.shop_name())

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 sm:py-16">
        <x-storefront.account-nav />
        <h1 class="text-2xl font-semibold tracking-tight text-night-950">Mon compte</h1>
        <p class="mt-2 text-sm text-night-800/70">Bienvenue {{ $user->name }}.</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <article class="rounded-2xl border border-night-900/10 bg-white p-5">
                <h2 class="font-medium">Informations</h2>
                <p class="mt-2 text-sm text-night-800/70">{{ $user->email }}</p>
                <p class="text-sm text-night-800/70">{{ $user->phone }}</p>
                <a href="{{ route('account.profile.edit') }}" class="mt-3 inline-flex text-sm font-semibold text-shop-orange">Modifier le profil</a>
            </article>
            <article class="rounded-2xl border border-night-900/10 bg-white p-5">
                <h2 class="font-medium">Commandes</h2>
                <p class="mt-2 text-sm text-night-800/70">Suivez vos achats, livraisons et paiements à la livraison.</p>
                <a href="{{ route('account.orders.index') }}" class="mt-3 inline-flex text-sm font-semibold text-shop-orange">Voir mes commandes</a>
            </article>
            <article class="rounded-2xl border border-night-900/10 bg-white p-5">
                <h2 class="font-medium">Favoris</h2>
                <p class="mt-2 text-sm text-night-800/70">Retrouvez les produits enregistrés depuis le catalogue.</p>
                <a href="{{ route('account.wishlist') }}" class="mt-3 inline-flex text-sm font-semibold text-shop-orange">Voir mes favoris</a>
            </article>
            <article class="rounded-2xl border border-night-900/10 bg-white p-5">
                <h2 class="font-medium">Adresses</h2>
                <p class="mt-2 text-sm text-night-800/70">Maison, bureau ou point de livraison à Abidjan.</p>
                <a href="{{ route('account.addresses.index') }}" class="mt-3 inline-flex text-sm font-semibold text-shop-orange">Gérer mes adresses</a>
            </article>
            <article class="rounded-2xl border border-[#25D366]/30 bg-[#25D366]/5 p-5 sm:col-span-2">
                <h2 class="font-medium">Commande WhatsApp</h2>
                <p class="mt-2 text-sm text-night-800/70">Passez une commande ou posez une question à un conseiller {{ shop_name() }}.</p>
                <x-storefront.whatsapp-order-button
                    class="mt-4"
                    :message="'Bonjour, je suis '.($user->name).' ('.$user->phone.') et je souhaite passer une commande sur '.shop_name().'.'"
                />
            </article>
        </div>
    </section>
@endsection
