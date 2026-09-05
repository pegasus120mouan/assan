@extends('layouts.admin')

@section('title', 'Importer des produits — '.shop_name())
@section('heading', 'Import CSV')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-night-900/10 bg-white p-6">
        <h1 class="text-xl font-semibold tracking-tight">Importer des produits</h1>
        <p class="mt-2 text-sm text-night-800/70">
            Fichier CSV encodé UTF-8, séparateur virgule ou point-virgule. Si le SKU existe déjà, le produit est mis à jour.
        </p>
        <p class="mt-3 rounded-xl bg-slate-50 px-4 py-3 font-mono text-xs text-night-800/80">
            name;sku;category_slug;brand_slug;selling_price;stock_quantity;status;featured;short_description
        </p>

        @if (session('import_errors'))
            <ul class="mt-4 space-y-1 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('admin.products.import.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="file" class="mb-1 block text-sm font-medium">Fichier CSV</label>
                <input id="file" name="file" type="file" accept=".csv,text/csv" required class="block w-full text-sm">
                @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-full bg-night-950 px-5 py-2.5 text-sm font-semibold text-white">Importer</button>
                <a href="{{ route('admin.products.index') }}" class="text-sm underline">Retour</a>
            </div>
        </form>
    </div>
@endsection
