@extends('layouts.admin')
@section('title', 'Modifier la promotion — '.shop_name())
@section('heading', 'Modifier la promotion')
@section('content')
    <form method="POST" action="{{ route('admin.promotions.update', $promotion) }}" class="admin-card max-w-xl space-y-4">
        @csrf
        @method('PUT')
        @include('admin.promotions._form', ['promotion' => $promotion])
        <button class="admin-btn h-10 bg-night-950 px-4 text-white">Mettre à jour</button>
    </form>
@endsection
