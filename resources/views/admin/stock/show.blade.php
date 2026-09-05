@extends('layouts.admin')

@section('title', 'Stock — '.$product->name.' — '.shop_name())
@section('heading', 'Stock')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.stock.index') }}" class="text-sm underline">Retour aux stocks</a>
        <h1 class="mt-3 text-2xl font-semibold tracking-tight">{{ $product->name }}</h1>
        <p class="mt-1 text-sm text-night-800/70">SKU {{ $product->sku }} · Disponible : {{ $product->availableStock() }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="rounded-2xl border border-night-900/10 bg-white p-6 lg:col-span-2">
            <h2 class="text-sm font-semibold">Nouveau mouvement</h2>
            <p class="mt-1 text-xs text-night-800/50">Le stock n’est modifié que via ce formulaire.</p>
            @if ($errors->any())
                <p class="mt-3 text-sm text-red-600">{{ $errors->first() }}</p>
            @endif
            <form method="POST" action="{{ route('admin.stock.store', $product) }}" class="mt-4 space-y-3">
                @csrf
                @if ($product->variants->isNotEmpty())
                    <div>
                        <label for="product_variant_id" class="mb-1 block text-sm font-medium">Variante</label>
                        <select id="product_variant_id" name="product_variant_id" required class="w-full rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
                            @foreach ($product->variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->name }} — {{ $variant->availableStock() }} dispo</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label for="type" class="mb-1 block text-sm font-medium">Type</label>
                    <select id="type" name="type" class="w-full rounded-xl border border-night-900/15 px-3 py-2.5 text-sm">
                        @foreach (\App\Enums\StockMovementType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('type', 'purchase') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="quantity" class="mb-1 block text-sm font-medium">Quantité</label>
                    <input id="quantity" name="quantity" type="number" min="0" required value="{{ old('quantity') }}"
                        class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
                    <p class="mt-1 text-xs text-night-800/50">Pour un ajustement, indiquez le nouveau stock cible.</p>
                </div>
                <div>
                    <label for="reason" class="mb-1 block text-sm font-medium">Motif</label>
                    <input id="reason" name="reason" type="text" value="{{ old('reason') }}"
                        class="w-full rounded-xl border border-night-900/15 px-4 py-2.5 text-sm outline-none ring-accent focus:ring-2">
                </div>
                <button type="submit" class="rounded-full bg-night-950 px-5 py-2.5 text-sm font-semibold text-white">Enregistrer</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-night-900/10 bg-white lg:col-span-3">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-night-800/50">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Qté</th>
                            <th class="px-4 py-3">Motif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-night-900/10">
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="px-4 py-3 text-night-800/70">{{ $movement->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    {{ $movement->type->label() }}
                                    @if ($movement->variant)
                                        <span class="block text-xs text-night-800/50">{{ $movement->variant->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $movement->quantity }}</td>
                                <td class="px-4 py-3 text-night-800/70">{{ $movement->reason ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-night-800/50">Aucun mouvement pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($movements->hasPages())
                <div class="border-t border-night-900/10 px-4 py-3">{{ $movements->links() }}</div>
            @endif
        </div>
    </div>
@endsection
