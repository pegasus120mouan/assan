@extends('layouts.admin')
@section('title', 'Nouvelle promotion — '.shop_name())
@section('heading', 'Nouvelle promotion')
@section('content')
    <form method="POST" action="{{ route('admin.promotions.store') }}" class="admin-card max-w-xl space-y-4">
        @csrf
        @include('admin.promotions._form')
        <button class="admin-btn h-10 bg-night-950 px-4 text-white">Enregistrer</button>
    </form>
@endsection
