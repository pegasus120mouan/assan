@props([
    'icon' => 'grid',
])

<svg {{ $attributes->class('h-5 w-5') }} fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
    @foreach (\App\Support\CategoryIcons::paths($icon) as $d)
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $d }}" />
    @endforeach
</svg>
