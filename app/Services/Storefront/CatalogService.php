<?php

namespace App\Services\Storefront;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CatalogService
{
    /**
     * @return Builder<Product>
     */
    public function publishedQuery(): Builder
    {
        return Product::query()
            ->published()
            ->with(['primaryImage', 'category', 'brand', 'variants'])
            ->withAvg(['reviews' => fn (Builder $query) => $query->approved()], 'rating')
            ->withCount(['reviews' => fn (Builder $query) => $query->approved()]);
    }

    public function paginate(Request $request, ?Category $category = null): LengthAwarePaginator
    {
        $query = $this->filtered($request, $category);

        $sort = $request->string('sort')->toString();

        match ($sort) {
            'price_asc' => $query->orderBy('selling_price'),
            'price_desc' => $query->orderByDesc('selling_price'),
            'newest' => $query->latest(),
            'best_sellers' => $query->orderByDesc('is_best_seller')->latest(),
            default => $query->orderByDesc('featured')->latest(),
        };

        return $query->paginate(12)->withQueryString();
    }

    /**
     * @return Collection<int, Product>
     */
    public function searchSuggestions(string $term, int $limit = 8): Collection
    {
        $like = '%'.addcslashes($term, '%_\\').'%';

        return $this->publishedQuery()
            ->where(function (Builder $query) use ($like): void {
                $query->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like))
                    ->orWhereHas('brand', fn (Builder $brand) => $brand->where('name', 'like', $like));
            })
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{
     *     categories: Collection<int, Category>,
     *     bestCategories: Collection<int, Category>,
     *     promoTiles: Collection<int, Category>,
     *     heroSlides: Collection<int, Product>,
     *     featured: Collection<int, Product>,
     *     latest: Collection<int, Product>,
     *     newArrivals: Collection<int, Product>,
     *     bestSellers: Collection<int, Product>,
     *     onSale: Collection<int, Product>,
     *     spotlight: Collection<int, Product>,
     *     spotlightTitle: string
     * }
     */
    public function homeSections(): array
    {
        $base = fn () => $this->publishedQuery();

        $categories = Category::query()
            ->active()
            ->roots()
            ->with(['coverProduct.primaryImage'])
            ->withCount(['products as published_products_count' => fn (Builder $query) => $query->published()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $heroSlides = $base()->featured()->latest()->take(3)->get();

        if ($heroSlides->isEmpty()) {
            $heroSlides = $base()->latest()->take(3)->get();
        }

        $latest = $base()->latest()->take(12)->get();
        $onSale = $base()->onSale()->latest()->take(12)->get();
        $newArrivals = $base()->newArrivals()->latest()->take(8)->get();

        return [
            'categories' => $categories,
            'bestCategories' => Category::query()
                ->active()
                ->with(['coverProduct.primaryImage'])
                ->withCount(['products as published_products_count' => fn (Builder $query) => $query->published()])
                ->orderByDesc('published_products_count')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->take(8)
                ->get(),
            'promoTiles' => $categories->take(2),
            'heroSlides' => $heroSlides,
            'featured' => $base()->featured()->latest()->take(8)->get(),
            'latest' => $latest,
            'newArrivals' => $newArrivals,
            'bestSellers' => $base()->bestSellers()->latest()->take(8)->get(),
            'onSale' => $onSale,
            'spotlight' => $onSale->isNotEmpty() ? $onSale : $latest,
            'spotlightTitle' => $onSale->isNotEmpty() ? 'Deals du Jour' : 'Sélection du moment',
        ];
    }

    /**
     * @return Collection<int, Category>
     */
    public function activeCategories(): Collection
    {
        return Category::query()
            ->active()
            ->roots()
            ->with(['children' => fn ($query) => $query->active()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Brand>
     */
    public function activeBrands(): Collection
    {
        return Brand::query()->active()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Product>
     */
    public function similar(Product $product, int $limit = 4): Collection
    {
        return $this->publishedQuery()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * @return array{previous: Product|null, next: Product|null}
     */
    public function neighbours(Product $product): array
    {
        return [
            'previous' => $this->publishedQuery()
                ->where('category_id', $product->category_id)
                ->where('id', '<', $product->id)
                ->orderByDesc('id')
                ->first(),
            'next' => $this->publishedQuery()
                ->where('category_id', $product->category_id)
                ->where('id', '>', $product->id)
                ->orderBy('id')
                ->first(),
        ];
    }

    /**
     * @return Builder<Product>
     */
    private function filtered(Request $request, ?Category $category): Builder
    {
        $query = $this->publishedQuery();

        if ($category) {
            $category->loadMissing('children');
            $query->whereIn('category_id', $category->subtreeIds());
        } elseif ($request->filled('category')) {
            $selected = Category::query()->active()->where('slug', $request->string('category'))->first();
            if ($selected) {
                $selected->loadMissing('children');
                $query->whereIn('category_id', $selected->subtreeIds());
            }
        }

        $query->when($request->filled('brand'), fn (Builder $builder) => $builder->where('brand_id', $request->integer('brand')))
            ->when($request->filled('min_price'), fn (Builder $builder) => $builder->where('selling_price', '>=', $request->integer('min_price')))
            ->when($request->filled('max_price'), fn (Builder $builder) => $builder->where('selling_price', '<=', $request->integer('max_price')))
            ->when($request->boolean('in_stock'), fn (Builder $builder) => $builder->inStock())
            ->when($request->boolean('on_sale'), fn (Builder $builder) => $builder->onSale())
            ->when($request->boolean('is_new'), fn (Builder $builder) => $builder->newArrivals())
            ->when($request->boolean('best_seller'), fn (Builder $builder) => $builder->bestSellers());

        $term = $request->query('q');

        if (is_string($term) && trim($term) !== '') {
            $like = '%'.addcslashes(mb_substr(trim($term), 0, 120), '%_\\').'%';
            $query->where(function (Builder $builder) use ($like): void {
                $builder->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like))
                    ->orWhereHas('brand', fn (Builder $brand) => $brand->where('name', 'like', $like));
            });
        }

        return $query;
    }
}
