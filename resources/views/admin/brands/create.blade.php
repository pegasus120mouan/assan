@extends('layouts.admin')

@section('title', 'Nouvelle marque — '.shop_name())
@section('heading', 'Nouvelle marque')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-night-900/10 bg-white p-6">
        <h1 class="text-xl font-semibold tracking-tight">Nouvelle marque</h1>
        <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="mt-6">
            @csrf
            @include('admin.brands._form', ['brand' => null])
        </form>
    </div>
@endsection
