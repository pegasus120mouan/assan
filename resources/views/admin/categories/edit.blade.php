@extends('layouts.admin')

@section('title', 'Modifier '.$category->name.' — '.shop_name())
@section('heading', 'Modifier la catégorie')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-night-900/10 bg-white p-6">
        <h1 class="text-xl font-semibold tracking-tight">Modifier {{ $category->name }}</h1>
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="mt-6">
            @csrf
            @method('PUT')
            @include('admin.categories._form', ['category' => $category, 'parents' => $parents])
        </form>
    </div>
@endsection
