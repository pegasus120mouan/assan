@extends('layouts.admin')
@section('title', 'Avis — '.shop_name())
@section('heading', 'Avis')
@section('content')
    <div class="mb-5"><h1 class="text-xl font-semibold">Avis</h1></div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <form method="GET" class="flex flex-wrap gap-2 border-b border-slate-200 bg-slate-50/80 p-3">
            <select name="status" class="h-9 rounded-md border border-slate-200 px-2 text-sm">
                <option value="">Tous</option>
                @foreach (\App\Enums\ReviewStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button class="admin-btn h-9 bg-night-950 px-3 text-white">Filtrer</button>
        </form>
        <table class="admin-table">
            <thead><tr><th>Produit</th><th>Client</th><th>Note</th><th>Statut</th><th></th></tr></thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td>{{ $review->product?->name }}</td>
                        <td>{{ $review->user?->name }}</td>
                        <td>{{ $review->rating }}/5 {{ $review->is_verified ? '· Vérifié' : '' }}</td>
                        <td>{{ $review->status->label() }}</td>
                        <td class="text-right">
                            @if ($review->status !== \App\Enums\ReviewStatus::Approved)
                                <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button class="text-sm text-emerald-700 underline">Approuver</button>
                                </form>
                            @endif
                            @if ($review->status !== \App\Enums\ReviewStatus::Rejected)
                                <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="text-sm text-red-600 underline">Rejeter</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-400">Aucun avis.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($reviews->hasPages())
            <div class="border-t px-4 py-3">{{ $reviews->links() }}</div>
        @endif
    </div>
@endsection
