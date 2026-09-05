@extends('layouts.storefront')

@section('title', 'Inscription — '.shop_name())

@section('content')
    <section class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="text-2xl font-semibold tracking-tight text-night-950">Créer un compte</h1>
        <p class="mt-2 text-sm text-night-800/70">Commandez plus vite et suivez vos livraisons à Abidjan.</p>

        <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
            @csrf

            <div>
                <label for="name" class="mb-1 block text-sm font-medium">Nom</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                    class="w-full rounded-xl border border-night-900/15 px-4 py-3 text-sm outline-none ring-accent focus:ring-2">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                    class="w-full rounded-xl border border-night-900/15 px-4 py-3 text-sm outline-none ring-accent focus:ring-2">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="mb-1 block text-sm font-medium">Téléphone</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel"
                    placeholder="07 00 00 00 00"
                    class="w-full rounded-xl border border-night-900/15 px-4 py-3 text-sm outline-none ring-accent focus:ring-2">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Mot de passe</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                    class="w-full rounded-xl border border-night-900/15 px-4 py-3 text-sm outline-none ring-accent focus:ring-2">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                    class="w-full rounded-xl border border-night-900/15 px-4 py-3 text-sm outline-none ring-accent focus:ring-2">
            </div>

            <button type="submit" class="w-full rounded-md bg-shop-green px-4 py-3 text-sm font-semibold text-white transition hover:bg-shop-green-dark">
                Créer mon compte
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="font-medium text-night-950 underline">Se connecter</a>
        </p>
    </section>
@endsection
