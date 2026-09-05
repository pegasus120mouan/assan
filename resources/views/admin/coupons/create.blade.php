@extends('layouts.admin')
@section('title', 'Nouveau coupon — '.shop_name())
@section('heading', 'Nouveau coupon')
@section('content')
    <form method="POST" action="{{ route('admin.coupons.store') }}" class="admin-card max-w-xl space-y-4">
        @csrf
        @include('admin.coupons._form')
        <button class="admin-btn h-10 bg-night-950 px-4 text-white">Enregistrer</button>
    </form>
@endsection
