@extends('layouts.admin')
@section('title', 'Coupons — '.shop_name())
@section('heading', 'Coupons')
@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Coupons</h1>
        <a href="{{ route('admin.coupons.create') }}" class="admin-btn bg-night-950 px-4 py-2 text-white">Nouveau coupon</a>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="admin-table">
            <thead><tr><th>Code</th><th>Type</th><th>Valeur</th><th>Utilisé</th><th>Statut</th><th></th></tr></thead>
            <tbody>
                @forelse ($coupons as $coupon)
                    <tr>
                        <td class="font-medium">{{ $coupon->code }}</td>
                        <td>{{ $coupon->type->label() }}</td>
                        <td>{{ $coupon->type === \App\Enums\CouponType::Percentage ? $coupon->value.' %' : format_price($coupon->value) }}</td>
                        <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                        <td>{{ $coupon->status->label() }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-sm underline">Modifier</a>
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-sm text-red-600 underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-400">Aucun coupon.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($coupons->hasPages())
            <div class="border-t px-4 py-3">{{ $coupons->links() }}</div>
        @endif
    </div>
@endsection
