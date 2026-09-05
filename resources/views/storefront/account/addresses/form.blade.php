@extends('layouts.storefront')

@section('title', ($address ? 'Modifier l’adresse' : 'Nouvelle adresse').' — '.shop_name())

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <x-storefront.account-nav />
        <h1 class="text-2xl font-semibold tracking-tight text-night-950">{{ $address ? 'Modifier l’adresse' : 'Nouvelle adresse' }}</h1>
        <form method="POST" action="{{ $address ? route('account.addresses.update', $address) : route('account.addresses.store') }}" class="mt-6 space-y-4 rounded-2xl border border-night-900/10 bg-white p-6">
            @csrf
            @if ($address)
                @method('PUT')
            @endif
            <div>
                <label class="mb-1 block text-sm font-medium">Libellé</label>
                <input name="label" value="{{ old('label', $address?->label ?? 'Maison') }}" required class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Destinataire</label>
                <input name="recipient_name" value="{{ old('recipient_name', $address?->recipient_name ?? auth()->user()->name) }}" required class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Téléphone</label>
                <input name="phone" value="{{ old('phone', $address?->phone ?? auth()->user()->phone) }}" required class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Adresse</label>
                <input name="address" value="{{ old('address', $address?->address) }}" required class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Commune</label>
                    <input name="commune" value="{{ old('commune', $address?->commune) }}" class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Ville</label>
                    <input name="city" value="{{ old('city', $address?->city ?? 'Abidjan') }}" class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Instructions</label>
                <textarea name="instructions" rows="2" class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm">{{ old('instructions', $address?->instructions) }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address?->is_default))>
                Adresse par défaut
            </label>
            <div class="flex gap-3">
                <button class="rounded-full bg-night-950 px-5 py-2.5 text-sm font-semibold text-white">Enregistrer</button>
                <a href="{{ route('account.addresses.index') }}" class="text-sm underline">Annuler</a>
            </div>
        </form>
    </section>
@endsection
