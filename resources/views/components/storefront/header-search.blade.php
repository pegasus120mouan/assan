@props([
    'placeholder' => 'Rechercher des produits…',
])

<div
    class="relative min-w-0 w-full"
    x-data="{
        q: @js(is_string(request('q')) ? request('q') : ''),
        open: false,
        products: [],
        categories: [],
        async suggest() {
            if (this.q.length < 2) { this.products = []; this.categories = []; this.open = false; return; }
            const response = await fetch('{{ route('search.suggest') }}?q=' + encodeURIComponent(this.q));
            const data = await response.json();
            this.products = data.products;
            this.categories = data.categories;
            this.open = this.products.length > 0 || this.categories.length > 0;
        }
    }"
    @focusin="open = products.length > 0 || categories.length > 0"
    @click.outside="open = false"
>
    <form method="GET" action="{{ route('search') }}" class="shop-search">
        <input
            name="q"
            x-model="q"
            @input.debounce.300ms="suggest"
            type="search"
            placeholder="{{ $placeholder }}"
        >
        <button type="submit" aria-label="Rechercher">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
        </button>
    </form>
    <div x-cloak x-show="open" class="absolute left-0 right-0 z-50 mt-1 overflow-hidden rounded-md border border-gray-100 bg-white shadow-lg">
        <template x-for="category in categories" :key="category.url">
            <a :href="category.url" class="block px-4 py-2 text-sm hover:bg-emerald-50" x-text="category.name"></a>
        </template>
        <template x-for="product in products" :key="product.url">
            <a :href="product.url" class="flex items-center justify-between gap-3 px-4 py-2 text-sm hover:bg-emerald-50">
                <span x-text="product.name"></span>
                <span class="shrink-0 text-xs font-semibold text-shop-orange" x-text="product.price"></span>
            </a>
        </template>
    </div>
</div>
