@extends('layouts.storefront')

@section('title', 'Mot de passe oublié — '.shop_name())

@section('content')
    <section class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="text-2xl font-semibold tracking-tight text-night-950">Mot de passe oublié</h1>
        <p class="mt-2 text-sm text-night-800/70">Indiquez votre e-mail pour recevoir un lien de réinitialisation.</p>

        @if (session('status'))
            <p class="mt-4 rounded-xl bg-shop-green/10 px-4 py-3 text-sm text-shop-green">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-xl border border-night-900/15 px-4 py-3 text-sm outline-none ring-accent focus:ring-2">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-md bg-shop-green px-4 py-3 text-sm font-semibold text-white transition hover:bg-shop-green-dark">
                Envoyer le lien
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            <a href="{{ route('login') }}" class="underline">Retour à la connexion</a>
        </p>
    </section>
@endsection
