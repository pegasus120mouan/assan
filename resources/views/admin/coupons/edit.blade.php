@extends('layouts.admin')
@section('title', 'Modifier le coupon — '.shop_name())
@section('heading', 'Modifier le coupon')
@section('content')
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="admin-card max-w-xl space-y-4">
        @csrf
        @method('PUT')
        @include('admin.coupons._form', ['coupon' => $coupon])
        <button class="admin-btn h-10 bg-night-950 px-4 text-white">Mettre à jour</button>
    </form>
@endsection
