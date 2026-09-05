<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration — '.shop_name())</title>
    <link rel="icon" type="image/png" href="{{ shop_logo_url() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-100 text-night-900 antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        <div
            x-cloak
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-30 bg-night-950/50 md:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col bg-night-950 text-white transition-transform md:static md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
        >
            <div class="border-b border-white/10 px-4 py-4">
                <x-shop-logo href="{{ route('admin.dashboard') }}" variant="on-dark" />
                <span class="mt-2 block px-1 text-xs text-white/50">Administration</span>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto p-4 text-sm">
                @php
                    $groups = collect(config('admin.navigation'))->groupBy('group');
                    $adminNavBadges = $adminNavBadges ?? [];
                @endphp

                @foreach ($groups as $group => $items)
                    <div>
                        <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/35">{{ $group }}</p>
                        <div class="space-y-1">
                            @foreach ($items as $item)
                                @continue(! empty($item['admin_only']) && ! auth()->user()?->isAdmin())
                                @php
                                    $activePattern = str_ends_with($item['route'], '.index')
                                        ? str_replace('.index', '.*', $item['route'])
                                        : $item['route'];
                                    $badgeCount = isset($item['badge'])
                                        ? (int) ($adminNavBadges[$item['badge']] ?? 0)
                                        : 0;
                                @endphp
                                <x-admin.nav-link
                                    :href="route($item['route'])"
                                    :active="request()->routeIs($activePattern)"
                                    :badge="$badgeCount"
                                >
                                    {{ $item['label'] }}
                                </x-admin.nav-link>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm text-white/70 hover:bg-white/5 hover:text-white">
                    Voir la boutique
                </a>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between gap-3 border-b border-night-900/10 bg-white px-4 py-3 sm:px-6">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-night-900/10 md:hidden"
                        @click="sidebarOpen = !sidebarOpen"
                        aria-label="Ouvrir le menu"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </button>
                    <p class="text-sm font-medium">@yield('heading', 'Administration')</p>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="hidden text-night-800/70 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-night-900/15 px-3 py-1.5 hover:bg-slate-50">Déconnexion</button>
                    </form>
                </div>
            </header>
            <main class="flex-1 p-4 sm:p-6">
                @if (session('status'))
                    <p class="mb-4 rounded-xl bg-accent-green/10 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-medium">Le formulaire n’a pas pu être enregistré.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <x-admin.confirm-delete />
    @stack('scripts')
</body>
</html>
