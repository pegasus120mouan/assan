@php
    $accountLinks = [
        ['route' => 'account.dashboard', 'pattern' => 'account.dashboard', 'label' => 'Aperçu'],
        ['route' => 'account.orders.index', 'pattern' => 'account.orders.*', 'label' => 'Commandes'],
        ['route' => 'account.wishlist', 'pattern' => 'account.wishlist', 'label' => 'Favoris'],
        ['route' => 'account.profile.edit', 'pattern' => 'account.profile.*', 'label' => 'Profil'],
        ['route' => 'account.addresses.index', 'pattern' => 'account.addresses.*', 'label' => 'Adresses'],
    ];
@endphp
<nav class="mb-8 flex flex-wrap gap-2 text-sm">
    @foreach ($accountLinks as $link)
        <a href="{{ route($link['route']) }}" @class([
            'rounded-full px-3 py-1.5',
            'bg-night-950 text-white' => request()->routeIs($link['pattern']),
            'border border-night-900/15 text-night-800/70 hover:text-shop-orange' => ! request()->routeIs($link['pattern']),
        ])>{{ $link['label'] }}</a>
    @endforeach
</nav>
