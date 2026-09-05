@props([
    'href' => null,
    'variant' => 'full',
])

<a
    href="{{ $href ?? route('home') }}"
    {{ $attributes->class(['shop-logo', 'shop-logo--'.$variant]) }}
    aria-label="{{ shop_name() }} — accueil"
>
    <img
        src="{{ shop_logo_url() }}"
        alt="{{ shop_name() }} — {{ shop_tagline() }}"
        class="shop-logo__img"
        width="220"
        height="102"
    >
</a>
