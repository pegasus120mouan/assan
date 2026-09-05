@extends('layouts.storefront')

@section('title', 'Mes adresses — '.shop_name())

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <x-storefront.account-nav />
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold tracking-tight text-night-950">Adresses</h1>
            <a href="{{ route('account.addresses.create') }}" class="rounded-full bg-night-950 px-4 py-2 text-sm font-semibold text-white">Ajouter</a>
        </div>
        <div class="mt-6 space-y-3">
            @forelse ($addresses as $address)
                <article class="rounded-2xl border border-night-900/10 bg-white p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ $address->label }} @if ($address->is_default)<span class="text-xs text-shop-orange">Par défaut</span>@endif</p>
                            <p class="mt-1 text-sm text-night-800/70">{{ $address->recipient_name }} · {{ $address->phone }}</p>
                            <p class="text-sm text-night-800/70">{{ $address->address }}, {{ $address->commune }} {{ $address->city }}</p>
                        </div>
                        <div class="flex gap-2 text-sm">
                            <a href="{{ route('account.addresses.edit', $address) }}" class="underline">Modifier</a>
                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Supprimer cette adresse ?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 underline">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-night-900/15 px-6 py-10 text-center text-sm text-night-800/60">Aucune adresse enregistrée.</p>
            @endforelse
        </div>
    </section>
@endsection
