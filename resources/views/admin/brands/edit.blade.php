@extends('layouts.admin')

@section('title', 'Modifier '.$brand->name.' — '.shop_name())
@section('heading', 'Modifier la marque')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-night-900/10 bg-white p-6">
        <h1 class="text-xl font-semibold tracking-tight">Modifier {{ $brand->name }}</h1>
        <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="mt-6">
            @csrf
            @method('PUT')
            @include('admin.brands._form', ['brand' => $brand])
        </form>
    </div>
@endsection
