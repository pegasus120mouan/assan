@extends('layouts.admin')

@section('title', $payment->reference.' — '.shop_name())
@section('heading', 'Paiement')

@section('content')
    <div class="mb-5 flex items-end justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ $payment->reference }}</h1>
            <p class="text-sm text-slate-500">{{ $payment->gateway->label() }} · {{ $payment->status->label() }}</p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="text-sm text-slate-500">← Liste</a>
    </div>
    <section class="admin-card max-w-xl">
        <p class="text-sm">Commande : <a class="font-medium" href="{{ $payment->order ? route('admin.orders.show', $payment->order) : '#' }}">{{ $payment->order?->order_number }}</a></p>
        <p class="mt-2 text-sm">Montant : {{ format_price($payment->amount) }}</p>
        @if ($payment->metadata['note'] ?? null)
            <p class="mt-2 text-sm text-slate-500">{{ $payment->metadata['note'] }}</p>
        @endif
        @if ($payment->status !== \App\Enums\PaymentStatus::Paid)
            <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="mt-4 flex gap-2">
                @csrf
                @method('PATCH')
                <button name="status" value="paid" class="admin-btn bg-emerald-700 px-3 text-white">Marquer payé</button>
                <button name="status" value="failed" class="admin-btn admin-btn--ghost">Marquer échoué</button>
            </form>
        @endif
    </section>
@endsection
