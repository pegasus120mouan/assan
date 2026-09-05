@props([
    'category',
])

<a href="{{ route('catalog.category', $category) }}" class="group block overflow-hidden bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="flex aspect-[4/3] items-center justify-center bg-gradient-to-br from-slate-100 to-orange-50">
        @if ($category->displayImageUrl())
            <img src="{{ $category->displayImageUrl() }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.04]">
        @else
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-shop-green text-2xl font-extrabold text-white">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
        @endif
    </div>
    <div class="px-4 py-3">
        <h3 class="font-bold text-night-950 group-hover:text-shop-orange">{{ $category->name }}</h3>
        <p class="mt-0.5 text-sm text-gray-500">
            @isset($category->published_products_count)
                {{ $category->published_products_count }} produit{{ $category->published_products_count > 1 ? 's' : '' }}
            @else
                Voir la catégorie
            @endisset
        </p>
    </div>
</a>
