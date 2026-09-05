@extends('layouts.admin')

@section('title', 'Nouveau produit — '.shop_name())
@section('heading', 'Nouveau produit')

@section('content')
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Nouveau produit</h1>
            <p class="mt-0.5 text-sm text-slate-500">Renseignez la fiche, les prix et les images.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-slate-500 hover:text-night-950">← Retour à la liste</a>
    </div>
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.products._form', ['product' => null])
    </form>
@endsection
