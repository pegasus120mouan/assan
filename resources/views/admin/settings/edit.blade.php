@extends('layouts.admin')
@section('title', 'Paramètres — '.shop_name())
@section('heading', 'Paramètres')
@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        <h1 class="text-xl font-semibold">Boutique</h1>
        <p class="mt-1 text-sm text-slate-500">Ces valeurs s’affichent sur le site. Les secrets (paiements, WhatsApp API) restent dans le fichier <code>.env</code>.</p>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium">Nom de la boutique</label>
                <input name="shop[name]" value="{{ old('shop.name', $values['shop.name']) }}" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Accroche</label>
                <input name="shop[tagline]" value="{{ old('shop.tagline', $values['shop.tagline']) }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">E-mail</label>
                <input name="shop[email]" type="email" value="{{ old('shop.email', $values['shop.email']) }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Téléphone commandes</label>
                    <input name="shop[phone]" value="{{ old('shop.phone', $values['shop.phone']) }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">SAV</label>
                    <input name="shop[sav]" value="{{ old('shop.sav', $values['shop.sav']) }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">WhatsApp (lien boutique)</label>
                <input name="shop[whatsapp]" value="{{ old('shop.whatsapp', $values['shop.whatsapp']) }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Adresse</label>
                <input name="shop[address]" value="{{ old('shop.address', $values['shop.address']) }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
            </div>
            <button class="rounded-full bg-night-950 px-5 py-2.5 text-sm font-semibold text-white">Enregistrer</button>
        </form>
    </div>
@endsection
