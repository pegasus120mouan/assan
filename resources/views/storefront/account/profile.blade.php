@extends('layouts.storefront')

@section('title', 'Mon profil — '.shop_name())

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <x-storefront.account-nav />
        <h1 class="text-2xl font-semibold tracking-tight text-night-950">Profil</h1>
        <form method="POST" action="{{ route('account.profile.update') }}" class="mt-6 space-y-4 rounded-2xl border border-night-900/10 bg-white p-6">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium">Nom</label>
                <input name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">E-mail</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Téléphone</label>
                <input name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Adresse</label>
                <input name="address" value="{{ old('address', $user->profile?->address) }}" class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Commune</label>
                    <input name="commune" value="{{ old('commune', $user->profile?->commune) }}" class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Ville</label>
                    <input name="city" value="{{ old('city', $user->profile?->city ?? 'Abidjan') }}" class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
                </div>
            </div>
            <button class="rounded-full bg-night-950 px-5 py-2.5 text-sm font-semibold text-white">Enregistrer</button>
        </form>
    </section>
@endsection
