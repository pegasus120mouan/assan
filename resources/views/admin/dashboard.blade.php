@extends('layouts.admin')

@section('title', 'Tableau de bord — '.shop_name())
@section('heading', 'Tableau de bord')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Bonjour {{ $user->name }}</h1>
        <p class="mt-1 text-sm text-night-800/70">Vue d'ensemble des ventes — {{ now()->translatedFormat('l d F Y') }}</p>
    </div>

    <section class="mb-8">
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-night-800/60">Aujourd'hui</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-2xl border border-night-900/10 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-night-800/50">Chiffre d'affaires</p>
                <p class="mt-2 text-xl font-semibold">{{ format_price($stats['today']['revenue']) }}</p>
            </article>
            <article class="rounded-2xl border border-night-900/10 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-night-800/50">Commandes</p>
                <p class="mt-2 text-xl font-semibold">{{ $stats['today']['orders'] }}</p>
            </article>
            <article class="rounded-2xl border border-night-900/10 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-night-800/50">Produits vendus</p>
                <p class="mt-2 text-xl font-semibold">{{ $stats['today']['products_sold'] }}</p>
            </article>
            <article class="rounded-2xl border border-night-900/10 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-night-800/50">Nouveaux clients</p>
                <p class="mt-2 text-xl font-semibold">{{ $stats['today']['new_customers'] }}</p>
            </article>
            <article class="rounded-2xl border border-night-900/10 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-night-800/50">Commandes en attente</p>
                <p class="mt-2 text-xl font-semibold">{{ $stats['today']['pending_orders'] }}</p>
            </article>
        </div>
    </section>

    <section class="mb-8 grid gap-4 lg:grid-cols-3">
        <article class="rounded-2xl border border-night-900/10 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-night-800/50">CA hebdomadaire</p>
            <p class="mt-2 text-xl font-semibold">{{ format_price($stats['revenue']['week']) }}</p>
        </article>
        <article class="rounded-2xl border border-night-900/10 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-night-800/50">CA mensuel</p>
            <p class="mt-2 text-xl font-semibold">{{ format_price($stats['revenue']['month']) }}</p>
        </article>
        <article class="rounded-2xl border border-night-900/10 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-night-800/50">CA journalier</p>
            <p class="mt-2 text-xl font-semibold">{{ format_price($stats['today']['revenue']) }}</p>
        </article>
    </section>

    <section class="mb-8 grid gap-6 xl:grid-cols-3">
        <article class="rounded-2xl border border-night-900/10 bg-white p-5 xl:col-span-2">
            <h2 class="text-sm font-semibold">Chiffre d'affaires — 7 derniers jours</h2>
            <div class="mt-4 h-64">
                <canvas id="revenue-chart"></canvas>
            </div>
        </article>
        <article class="rounded-2xl border border-night-900/10 bg-white p-5">
            <h2 class="text-sm font-semibold">Commandes par statut</h2>
            <div class="mt-4 h-64">
                <canvas id="status-chart"></canvas>
            </div>
        </article>
    </section>

    <section class="mb-8 grid gap-6 xl:grid-cols-2">
        <article class="rounded-2xl border border-night-900/10 bg-white p-5">
            <h2 class="text-sm font-semibold">Ventes par catégorie (mois)</h2>
            <div class="mt-4 h-64">
                <canvas id="category-chart"></canvas>
            </div>
            @if ($stats['sales_by_category']->isEmpty())
                <p class="mt-2 text-sm text-night-800/50">Aucune vente enregistrée ce mois-ci.</p>
            @endif
        </article>
        <article class="rounded-2xl border border-night-900/10 bg-white p-5">
            <h2 class="text-sm font-semibold">Produits les plus vendus</h2>
            @if ($stats['top_products']->isEmpty())
                <p class="mt-4 text-sm text-night-800/50">Pas encore de ventes.</p>
            @else
                <ul class="mt-4 divide-y divide-night-900/10 text-sm">
                    @foreach ($stats['top_products'] as $product)
                        <li class="flex items-center justify-between py-3">
                            <div>
                                <p class="font-medium">{{ $product['name'] }}</p>
                                <p class="text-xs text-night-800/50">{{ $product['sku'] }} · {{ $product['quantity'] }} vendus</p>
                            </div>
                            <p class="font-medium">{{ format_price($product['revenue']) }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>
    </section>

    <section class="rounded-2xl border border-night-900/10 bg-white p-5">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold">Dernières commandes</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-night-800/60 hover:text-night-950">Voir tout</a>
        </div>
        @if ($stats['recent_orders']->isEmpty())
            <p class="text-sm text-night-800/50">Aucune commande pour le moment.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-wide text-night-800/50">
                        <tr>
                            <th class="pb-3 pr-4">N°</th>
                            <th class="pb-3 pr-4">Client</th>
                            <th class="pb-3 pr-4">Montant</th>
                            <th class="pb-3 pr-4">Paiement</th>
                            <th class="pb-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-night-900/10">
                        @foreach ($stats['recent_orders'] as $order)
                            <tr>
                                <td class="py-3 pr-4 font-medium">{{ $order->order_number }}</td>
                                <td class="py-3 pr-4">{{ $order->customer_name }}</td>
                                <td class="py-3 pr-4">{{ format_price($order->total) }}</td>
                                <td class="py-3 pr-4">{{ $order->payment_status->label() }}</td>
                                <td class="py-3">{{ $order->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.createAdminCharts === 'function') {
                window.createAdminCharts(@json($charts));
            }
        });
    </script>
@endpush
