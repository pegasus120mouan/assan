@props([
    'href',
    'active' => false,
    'badge' => 0,
])

@php
    $count = (int) $badge;
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'flex items-center justify-between gap-2 rounded-lg px-3 py-2 transition',
        'bg-white/10 text-white' => $active,
        'text-white/70 hover:bg-white/5 hover:text-white' => ! $active,
    ]) }}
>
    <span>{{ $slot }}</span>
    @if ($count > 0)
        <span
            class="admin-nav-badge"
            aria-label="{{ $count }} commande{{ $count > 1 ? 's' : '' }} en attente"
        >{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</a>
