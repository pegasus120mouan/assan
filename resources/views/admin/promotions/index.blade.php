@extends('layouts.admin')
@section('title', 'Promotions — '.shop_name())
@section('heading', 'Promotions')
@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Promotions</h1>
        <a href="{{ route('admin.promotions.create') }}" class="admin-btn bg-night-950 px-4 py-2 text-white">Nouvelle promotion</a>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="admin-table">
            <thead><tr><th>Nom</th><th>Cible</th><th>Remise</th><th>Statut</th><th></th></tr></thead>
            <tbody>
                @forelse ($promotions as $promotion)
                    <tr>
                        <td class="font-medium">{{ $promotion->name }}</td>
                        <td>{{ $promotion->product?->name ?? $promotion->category?->name ?? $promotion->type->label() }}</td>
                        <td>
                            @if ($promotion->discount_type === \App\Enums\DiscountType::Percentage)
                                {{ $promotion->value }} %
                            @elseif ($promotion->discount_type === \App\Enums\DiscountType::PromotionalPrice)
                                {{ format_price($promotion->value) }}
                            @else
                                −{{ format_price($promotion->value) }}
                            @endif
                        </td>
                        <td>{{ $promotion->status->label() }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.promotions.edit', $promotion) }}" class="text-sm underline">Modifier</a>
                            <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-sm text-red-600 underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-400">Aucune promotion.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($promotions->hasPages())
            <div class="border-t px-4 py-3">{{ $promotions->links() }}</div>
        @endif
    </div>
@endsection
