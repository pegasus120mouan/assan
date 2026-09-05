@extends('layouts.storefront')

@section('title', 'Connexion — '.shop_name())

@section('content')
    <section class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="text-2xl font-semibold tracking-tight text-night-950">Connexion</h1>
        <p class="mt-2 text-sm text-night-800/70">Accédez à votre compte {{ shop_name() }}.</p>

        @if (session('status'))
            <p class="mt-4 rounded-xl bg-shop-green/10 px-4 py-3 text-sm text-shop-green">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
            @csrf

            <div>
                <label for="login" class="mb-1 block text-sm font-medium">E-mail ou téléphone</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username"
                    class="w-full rounded-xl border border-night-900/15 px-4 py-3 text-sm outline-none ring-accent focus:ring-2">
                @error('login')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Mot de passe</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                    class="w-full rounded-xl border border-night-900/15 px-4 py-3 text-sm outline-none ring-accent focus:ring-2">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-night-800/80">
                <input type="checkbox" name="remember" value="1" class="rounded border-night-900/20 text-accent focus:ring-accent">
                Se souvenir de moi
            </label>

            <button type="submit" class="w-full rounded-md bg-shop-green px-4 py-3 text-sm font-semibold text-white transition hover:bg-shop-green-dark">
                Se connecter
            </button>
        </form>

        <div class="mt-6 space-y-2 text-center text-sm">
            <p>
                <a href="{{ route('password.request') }}" class="text-night-800/70 underline hover:text-night-950">Mot de passe oublié ?</a>
            </p>
            <p>
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="font-medium text-night-950 underline">Créer un compte</a>
            </p>
        </div>
    </section>
@endsection
