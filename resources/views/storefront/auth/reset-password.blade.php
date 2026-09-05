@extends('layouts.storefront')

@section('title', 'Réinitialiser le mot de passe — '.shop_name())

@section('content')
    <section class="mx-auto max-w-md px-4 py-12 sm:px-6 sm:py-16">
        <h1 class="text-2xl font-semibold tracking-tight text-night-950">Nouveau mot de passe</h1>

        <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required
                    class="w-full rounded-xl border border-night-900/15 px-4 py-3 text-sm outline-none ring-accent focus:ring-2">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Nouveau mot de passe</label>
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
                Enregistrer le mot de passe
            </button>
        </form>
    </section>
@endsection
