@extends('layouts.admin')

@section('title', 'Frais de livraison — '.shop_name())
@section('heading', 'Frais de livraison')

@section('content')
    <div x-data="{ modal: {{ \Illuminate\Support\Js::from($initialModal) }} }" @keydown.escape.window="modal = null">
        <div class="mb-5 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Frais de livraison</h1>
            <button type="button" class="admin-btn bg-night-950 px-4 py-2 text-white" @click="modal = 'create'">Nouveau tarif</button>
        </div>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th>Mode</th>
                        <th>Frais</th>
                        <th>Gratuit dès</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fees as $fee)
                        <tr>
                            <td>{{ $fee->commune?->name ?? $fee->zone?->name ?? $fee->city?->name ?? 'Général' }}</td>
                            <td>{{ $fee->delivery_method->label() }}</td>
                            <td>{{ format_price($fee->fee) }}</td>
                            <td>{{ $fee->free_above_amount ? format_price($fee->free_above_amount) : '—' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-night-800/70 hover:bg-slate-100 hover:text-night-950"
                                        @click="modal = {{ $fee->id }}"
                                        aria-label="Modifier"
                                        title="Modifier"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 3.487a2.1 2.1 0 1 1 2.97 2.97L8.25 18.04l-3.97.99.99-3.97 11.592-11.573z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50"
                                        aria-label="Supprimer"
                                        title="Supprimer"
                                        @click="$dispatch('admin-confirm-delete', {{ \Illuminate\Support\Js::from([
                                            'title' => 'Supprimer le tarif',
                                            'message' => 'Supprimer ce tarif de livraison ?',
                                            'action' => route('admin.delivery-fees.destroy', $fee),
                                        ]) }})"
                                    >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3h6m-8 4h10m-9 0 .7 12.1a1.5 1.5 0 0 0 1.5 1.4h4.6a1.5 1.5 0 0 0 1.5-1.4L16 7M10 11v6m4-6v6" />
                                            </svg>
                                        </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-slate-400">Aucun tarif. Le forfait {{ format_price(config('delivery.standard_fee')) }} s’applique.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($fees->hasPages())
                <div class="border-t px-4 py-3">{{ $fees->links() }}</div>
            @endif
        </div>

        @include('admin.delivery-fees._modal', [
            'show' => "modal === 'create'",
            'title' => 'Nouveau tarif',
            'subtitle' => 'Ajoutez un tarif de livraison pour une commune, une ville ou une zone.',
            'action' => route('admin.delivery-fees.store'),
            'method' => 'POST',
            'fee' => null,
            'idPrefix' => 'create-',
            'hiddenName' => '_create_modal',
            'hiddenValue' => '1',
            'showErrors' => (bool) old('_create_modal'),
        ])

        @foreach ($modalFees as $modalFee)
            @include('admin.delivery-fees._modal', [
                'show' => 'modal === '.$modalFee->id,
                'title' => 'Modifier le tarif',
                'subtitle' => $modalFee->commune?->name ?? $modalFee->zone?->name ?? $modalFee->city?->name ?? 'Tarif général',
                'action' => route('admin.delivery-fees.update', $modalFee),
                'method' => 'PUT',
                'fee' => $modalFee,
                'idPrefix' => 'edit-'.$modalFee->id.'-',
                'hiddenName' => '_edit_modal',
                'hiddenValue' => $modalFee->id,
                'showErrors' => (string) old('_edit_modal') === (string) $modalFee->id,
            ])
        @endforeach
    </div>
@endsection
