@extends('layouts.admin')

@section('title', $title.' — '.shop_name())
@section('heading', $title)

@section('content')
    <div class="rounded-2xl border border-night-900/10 bg-white p-8">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
        <p class="mt-2 max-w-xl text-sm text-night-800/70">
            Ce module sera disponible dans une prochaine phase. Le tableau de bord et la navigation sont déjà en place.
        </p>
        <a href="{{ route('admin.dashboard') }}" class="mt-6 inline-flex rounded-full bg-night-950 px-4 py-2 text-sm font-semibold text-white">
            Retour au tableau de bord
        </a>
    </div>
@endsection
