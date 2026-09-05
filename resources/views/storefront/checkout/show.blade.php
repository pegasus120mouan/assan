@extends('layouts.storefront')

@section('title', 'Commande — '.shop_name())

@section('content')
    <section
        class="mx-auto max-w-7xl px-4 py-8 sm:px-6"
        x-data="{
            quotes: {{ \Illuminate\Support\Js::from($communeQuotes) }},
            defaultFee: {{ $defaultFee }},
            subtotal: {{ $totals['subtotal'] }},
            discount: {{ $totals['discount'] }},
            communeId: '{{ old('delivery_commune_id', $communes->first()?->id) }}',
            get delivery() {
                return this.quotes[this.communeId] ?? this.defaultFee;
            },
            get total() {
                return Math.max(0, this.subtotal - this.discount + this.delivery);
            },
            format(amount) {
                return new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA';
            }
        }"
    >
        <h1 class="text-2xl font-extrabold text-night-950">Finaliser la commande</h1>
        <p class="mt-1 text-sm text-slate-500">Livraison à Abidjan · Paiement à la livraison ou Mobile Money</p>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf
            <div class="space-y-5">
                <section class="bg-white p-5 shadow-sm">
                    <h2 class="font-bold">Informations client</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="admin-label" for="customer_name">Nom</label>
                            <input id="customer_name" name="customer_name" required value="{{ old('customer_name', $user?->name) }}" class="admin-field">
                        </div>
                        <div>
                            <label class="admin-label" for="customer_phone">Téléphone</label>
                            <input id="customer_phone" name="customer_phone" required value="{{ old('customer_phone', $user?->phone) }}" class="admin-field" placeholder="07 00 00 00 00">
                        </div>
                        <div>
                            <label class="admin-label" for="customer_email">E-mail</label>
                            <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email', $user?->email) }}" class="admin-field">
                        </div>
                    </div>
                </section>

                <section class="bg-white p-5 shadow-sm">
                    <h2 class="font-bold">Adresse de livraison</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="admin-label" for="delivery_address">Adresse</label>
                            <input id="delivery_address" name="delivery_address" required value="{{ old('delivery_address') }}" class="admin-field">
                        </div>
                        <div>
                            <label class="admin-label" for="delivery_commune_id">Commune</label>
                            @if ($communes->isNotEmpty())
                                <select id="delivery_commune_id" name="delivery_commune_id" class="admin-field" x-model="communeId">
                                    @foreach ($communes as $commune)
                                        <option value="{{ $commune->id }}" @selected((string) old('delivery_commune_id') === (string) $commune->id)>{{ $commune->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input id="delivery_commune" name="delivery_commune" value="{{ old('delivery_commune') }}" class="admin-field" placeholder="Cocody, Yopougon…">
                            @endif
                        </div>
                        <div>
                            <label class="admin-label" for="delivery_city">Ville</label>
                            <input id="delivery_city" name="delivery_city" required value="{{ old('delivery_city', 'Abidjan') }}" class="admin-field">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="admin-label" for="customer_notes">Notes</label>
                            <textarea id="customer_notes" name="customer_notes" rows="3" class="admin-field">{{ old('customer_notes') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="bg-white p-5 shadow-sm">
                    <h2 class="font-bold">Livraison</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                            <input type="radio" name="delivery_method" value="standard" checked>
                            <span>
                                <span class="block font-semibold">Livraison standard</span>
                                <span class="text-slate-500">Abidjan — <span x-text="format(delivery)">{{ format_price($totals['delivery']) }}</span></span>
                            </span>
                        </label>
                    </div>
                </section>

                <section class="bg-white p-5 shadow-sm">
                    <h2 class="font-bold">Paiement</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        @foreach ($gateways as $gateway)
                            <label class="flex items-start gap-3 rounded-lg border p-3 {{ $gateway->key()->value === 'cash_on_delivery' ? 'border-shop-green bg-emerald-50' : 'border-slate-200' }}">
                                <input type="radio" name="payment_method" value="{{ $gateway->key()->value }}" @checked($loop->first || $gateway->key()->value === 'cash_on_delivery')>
                                <span>
                                    <span class="block font-semibold">{{ $gateway->key()->label() }}</span>
                                    @if ($gateway->key()->value !== 'cash_on_delivery')
                                        <span class="text-slate-500">Confirmation par nos équipes. L’API n’est pas encore branchée.</span>
                                    @else
                                        <span class="text-slate-500">Payez une fois le colis reçu.</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="h-fit bg-white p-5 shadow-sm">
                <h2 class="font-bold">Votre commande</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($cart->items as $item)
                        <li class="flex justify-between gap-3">
                            <span>{{ $item->quantity }} × {{ $item->product?->name }}</span>
                            <span class="whitespace-nowrap font-medium">{{ format_price($item->subtotal()) }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-4 space-y-2 border-t border-slate-200 pt-4 text-sm">
                    <div class="flex justify-between"><dt>Sous-total</dt><dd>{{ format_price($totals['subtotal']) }}</dd></div>
                    <div class="flex justify-between"><dt>Réduction</dt><dd>{{ format_price($totals['discount']) }}</dd></div>
                    <div class="flex justify-between"><dt>Livraison</dt><dd x-text="format(delivery)">{{ format_price($totals['delivery']) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-200 pt-3 text-base font-bold"><dt>Total</dt><dd class="text-shop-orange" x-text="format(total)">{{ format_price($totals['total']) }}</dd></div>
                </dl>
                <button class="mt-5 inline-flex w-full items-center justify-center rounded bg-shop-green px-4 py-3 text-sm font-bold uppercase tracking-wide text-white hover:bg-shop-green-dark">
                    Confirmer la commande
                </button>
            </aside>
        </form>
    </section>
@endsection
