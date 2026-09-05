@extends('layouts.admin')

@section('title', 'Nouvelle catégorie — '.shop_name())
@section('heading', 'Nouvelle catégorie')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-night-900/10 bg-white p-6">
        <h1 class="text-xl font-semibold tracking-tight">Nouvelle catégorie</h1>
        <p class="mt-1 text-sm text-night-800/60">Le nom et une icône sont obligatoires : l’icône apparaît dans le menu de la boutique.</p>
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="mt-6">
            @csrf
            @include('admin.categories._form', ['category' => null, 'parents' => $parents])
        </form>
    </div>
@endsection
