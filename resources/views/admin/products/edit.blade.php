@extends('layouts.admin')

@section('title', 'Modifier '.$product->name.' — '.shop_name())
@section('heading', 'Modifier le produit')

@section('content')
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h1 class="truncate text-xl font-semibold tracking-tight">Modifier {{ $product->name }}</h1>
            <p class="mt-0.5 text-sm text-slate-500">SKU {{ $product->sku }}</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-slate-500 hover:text-night-950">← Retour à la liste</a>
    </div>
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.products._form', ['product' => $product])
    </form>
@endsection
