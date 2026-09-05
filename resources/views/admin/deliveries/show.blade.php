@extends('layouts.admin')

@section('title', $delivery->tracking_number.' — '.shop_name())
@section('heading', 'Livraison')

@section('content')
    <div class="mb-5 flex items-end justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ $delivery->tracking_number }}</h1>
            <p class="text-sm text-slate-500">{{ $delivery->provider->label() }} · {{ $delivery->status->label() }}</p>
        </div>
        <a href="{{ route('admin.deliveries.index') }}" class="text-sm text-slate-500">← Liste</a>
    </div>
    <section class="admin-card max-w-xl">
        <p class="text-sm">Commande : <a class="font-medium" href="{{ $delivery->order ? route('admin.orders.show', $delivery->order) : '#' }}">{{ $delivery->order?->order_number }}</a></p>
        <p class="mt-2 text-sm">Frais : {{ format_price($delivery->delivery_fee) }}</p>
        <form method="POST" action="{{ route('admin.deliveries.update', $delivery) }}" class="mt-4 space-y-3">
            @csrf
            @method('PATCH')
            <select name="status" class="admin-field">
                @foreach (\App\Enums\DeliveryStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($delivery->status === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <select name="assigned_to" class="admin-field">
                <option value="">Non assignée</option>
                @foreach ($staff as $member)
                    <option value="{{ $member->id }}" @selected((int) $delivery->assigned_to === $member->id)>{{ $member->name }}</option>
                @endforeach
            </select>
            <input name="failure_reason" value="{{ old('failure_reason', $delivery->failure_reason) }}" class="admin-field" placeholder="Motif d’échec (optionnel)">
            <button class="admin-btn h-10 w-full bg-night-950 text-white">Mettre à jour</button>
        </form>
    </section>
@endsection
