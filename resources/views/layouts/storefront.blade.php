@php
    $orderPhone = filled(config('shop.contact.phone')) ? config('shop.contact.phone') : (filled(config('shop.contact.whatsapp')) ? config('shop.contact.whatsapp') : '07 00 00 00 00');
    $savPhone = filled(config('shop.contact.sav')) ? config('shop.contact.sav') : $orderPhone;
    $orderTel = preg_replace('/\D+/', '', (string) $orderPhone);
    $savTel = preg_replace('/\D+/', '', (string) $savPhone);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', shop_name())</title>
    <meta name="description" content="@yield('meta_description', shop_tagline())">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('og_title', shop_name())">
    <meta property="og:description" content="@yield('og_description', shop_tagline())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <link rel="icon" type="image/png" href="{{ shop_logo_url() }}">
    <link rel="apple-touch-icon" href="{{ shop_logo_url() }}">
    <meta property="og:image" content="@yield('og_image', shop_logo_url())">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-[#f3f4f6] text-night-900 antialiased">
    <div class="flex min-h-screen flex-col" x-data="{ mobileMenuOpen: false, catsOpen: false }">
        <header class="sticky top-0 z-40 bg-white shadow-sm">
            <div class="border-b border-gray-100">
                <div class="mx-auto flex max-w-7xl items-center justify-end gap-4 px-4 py-1.5 text-xs text-gray-500 sm:px-6">
                    @auth
                        @if (auth()->user()->isStaff())
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-shop-orange">Admin</a>
                        @endif
                        <a href="{{ route('account.dashboard') }}" class="hover:text-shop-orange">Mon compte</a>
                        <a href="{{ route('account.wishlist') }}" class="hover:text-shop-orange">Favoris</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="hover:text-shop-orange">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-shop-orange">Se connecter</a>
                        <a href="{{ route('register') }}" class="font-semibold text-shop-orange">Créer un compte</a>
                    @endauth
                </div>
            </div>

            <div class="shop-header-row mx-auto max-w-7xl px-4 py-3 sm:px-6">
                <x-shop-logo />

                <div class="shop-header-search min-w-0">
                    <x-storefront.header-search />
                </div>

                <div class="hidden items-center gap-5 lg:flex">
                    <a href="tel:{{ $orderTel }}" class="shop-contact">
                        <span class="shop-contact__icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                        </span>
                        <span class="leading-tight">
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400">Commandez au</span>
                            <span class="block text-sm font-bold">{{ $orderPhone }}</span>
                        </span>
                    </a>
                    <a href="tel:{{ $savTel }}" class="shop-contact">
                        <span class="shop-contact__icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.42 15.17l-4.76-4.76a2 2 0 010-2.83l.7-.7a2 2 0 012.83 0l4.76 4.76m-3.53 3.53l4.76 4.76a2 2 0 002.83 0l.7-.7a2 2 0 000-2.83l-4.76-4.76M6 18l-3 3" /></svg>
                        </span>
                        <span class="leading-tight">
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-gray-400">SAV</span>
                            <span class="block text-sm font-bold">{{ $savPhone }}</span>
                        </span>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('cart.index') }}" class="relative flex items-center gap-2 text-night-950" title="Panier">
                        <span class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h15l-1.5 9h-12zM6 6L5 3H2m6 16a1 1 0 100 2 1 1 0 000-2zm10 0a1 1 0 100 2 1 1 0 000-2z" />
                            </svg>
                            <span class="shop-cart-badge">{{ $cartCount ?? 0 }}</span>
                        </span>
                        <span class="hidden leading-tight lg:block">
                            <span class="block text-xs text-gray-400">{{ $cartCount ?? 0 }} article{{ ($cartCount ?? 0) > 1 ? 's' : '' }}</span>
                            <span class="block text-sm font-bold">{{ format_price($cartTotal ?? 0) }}</span>
                        </span>
                    </a>

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-200 md:hidden"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen"
                        aria-label="Ouvrir le menu"
                    >
                        <svg x-show="!mobileMenuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                        <svg x-cloak x-show="mobileMenuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="border-t border-gray-100">
                <div class="mx-auto flex max-w-7xl items-stretch px-4 sm:px-6">
                    <div class="relative hidden md:block" @mouseenter="catsOpen = true" @mouseleave="catsOpen = false">
                        <button type="button" class="shop-cats-btn">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                            Catégories
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            x-cloak
                            x-show="catsOpen"
                            x-transition.opacity
                            class="absolute left-0 top-full z-50 w-[min(72rem,calc(100vw-2rem))] border border-t-0 border-gray-100 bg-white p-6 shadow-xl"
                        >
                            @if ($navCategories->isEmpty())
                                <a href="{{ route('catalog.index') }}" class="text-sm text-gray-500 hover:text-shop-orange">Voir le catalogue</a>
                            @else
                                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                    @foreach ($navCategories as $navCategory)
                                        <div>
                                            <a href="{{ route('catalog.category', $navCategory) }}" class="flex items-center gap-3 text-sm font-semibold text-night-950 hover:text-shop-orange">
                                                <x-category-icon :icon="$navCategory->icon" class="h-4 w-4 shrink-0 text-shop-green" />
                                                <span>{{ $navCategory->name }}</span>
                                            </a>
                                            @if ($navCategory->children->isNotEmpty())
                                                <ul class="mt-2 space-y-1">
                                                    @foreach ($navCategory->children as $child)
                                                        <li>
                                                            <a href="{{ route('catalog.category', $child) }}" class="text-sm text-gray-500 hover:text-shop-orange">{{ $child->name }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <nav class="hidden items-center gap-7 px-6 text-sm font-semibold text-gray-700 md:flex">
                        <a href="{{ route('catalog.index', ['on_sale' => 1]) }}" class="py-3 hover:text-shop-orange">Deals du jour</a>
                        <a href="{{ route('catalog.index', ['is_new' => 1]) }}" class="py-3 hover:text-shop-orange">Nouvel Arrivage</a>
                        <a href="{{ route('catalog.index') }}" class="py-3 hover:text-shop-orange">Catalogue</a>
                        <a href="#contact" class="py-3 hover:text-shop-orange">Contactez-nous</a>
                    </nav>
                </div>
            </div>

            <div x-cloak x-show="mobileMenuOpen" x-transition class="border-t border-gray-100 bg-white px-4 py-4 md:hidden">
                <nav class="flex flex-col text-sm font-medium">
                    <a href="{{ route('home') }}" class="py-2" @click="mobileMenuOpen = false">Accueil</a>
                    <a href="{{ route('catalog.index', ['on_sale' => 1]) }}" class="py-2" @click="mobileMenuOpen = false">Deals du jour</a>
                    <a href="{{ route('catalog.index', ['is_new' => 1]) }}" class="py-2" @click="mobileMenuOpen = false">Nouvel Arrivage</a>
                    <a href="{{ route('catalog.index') }}" class="py-2" @click="mobileMenuOpen = false">Catalogue</a>
                    <a href="#contact" class="py-2" @click="mobileMenuOpen = false">Contactez-nous</a>
                    @foreach ($navCategories as $navCategory)
                        <a href="{{ route('catalog.category', $navCategory) }}" class="flex items-center gap-3 py-2 text-gray-600" @click="mobileMenuOpen = false">
                            <x-category-icon :icon="$navCategory->icon" class="h-4 w-4 shrink-0 text-shop-green" />
                            <span>{{ $navCategory->name }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
            <div class="shop-flag"></div>
        </header>

        <main class="flex-1">
            @if (session('status') || $errors->any())
                <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6">
                    @if (session('status'))
                        <p class="mb-3 rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
                    @endif
                    @if ($errors->any())
                        <div class="mb-3 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                            <ul class="list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="mt-10 bg-white" id="contact">
            <div class="bg-[#111827] py-10 text-white">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-3">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-shop-orange">Contactez-nous</p>
                        <p class="mt-3 text-lg font-bold">{{ $orderPhone }}</p>
                        <p class="mt-2 text-sm text-white/70">{{ config('shop.contact.email') }}</p>
                        <p class="mt-3 text-xs text-white/50">Lundi – vendredi, 8h à 19h · Samedi, 8h à 17h</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-shop-orange">Service Après-Vente (SAV)</p>
                        <p class="mt-3 text-lg font-bold">{{ $savPhone }}</p>
                        <p class="mt-3 text-xs text-white/50">Lundi – vendredi, 8h à 17h · Samedi, 8h à 14h</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-shop-orange">{{ config('shop.contact.city') }}, {{ config('shop.country_name') }}</p>
                        @if (filled(config('shop.contact.address')))
                            <p class="mt-3 text-sm text-white/70">{{ config('shop.contact.address') }}</p>
                        @endif
                        <p class="mt-3 text-sm text-white/70">Livraison à Abidjan et partout en Côte d’Ivoire. Paiement à la livraison et Mobile Money à venir.</p>
                    </div>
                </div>
            </div>
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3">
                <div>
                    <x-shop-logo />
                    <p class="mt-3 max-w-sm text-sm text-gray-500">{{ shop_tagline() }}</p>
                </div>
                <div class="text-sm">
                    <p class="font-bold">Boutique</p>
                    <ul class="mt-3 space-y-2 text-gray-500">
                        <li><a href="{{ route('catalog.index') }}" class="hover:text-shop-orange">Catalogue</a></li>
                        <li><a href="{{ route('catalog.index', ['on_sale' => 1]) }}" class="hover:text-shop-orange">Deals du jour</a></li>
                        <li><a href="{{ route('catalog.index', ['is_new' => 1]) }}" class="hover:text-shop-orange">Nouvel Arrivage</a></li>
                    </ul>
                </div>
                <div class="text-sm">
                    <p class="font-bold">À propos</p>
                    <ul class="mt-3 space-y-2 text-gray-500">
                        <li><a href="#contact" class="hover:text-shop-orange">Contactez-nous</a></li>
                        <li><a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="hover:text-shop-orange">Mon compte</a></li>
                    </ul>
                </div>
            </div>
            <div class="shop-flag"></div>
            <div class="border-t border-gray-100 py-4 text-center text-xs text-gray-400">
                © {{ date('Y') }} {{ shop_name() }}. Tous droits réservés.
            </div>
        </footer>
    </div>

    <x-whatsapp-button />

    @stack('scripts')
</body>
</html>
