<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class StorefrontCache
{
    public const NAV_CATEGORIES = 'storefront.nav_category_ids';

    /**
     * @return Collection<int, Category>
     */
    public static function navCategories(): Collection
    {
        $ids = Cache::get(self::NAV_CATEGORIES);

        if (! is_array($ids)) {
            Cache::forget(self::NAV_CATEGORIES);
            Cache::forget('storefront.nav_categories');

            $ids = Category::query()
                ->active()
                ->roots()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('id')
                ->all();

            Cache::put(self::NAV_CATEGORIES, $ids, 3600);
        }

        if ($ids === []) {
            return collect();
        }

        $categories = Category::query()
            ->active()
            ->whereIn('id', $ids)
            ->with(['children' => fn ($query) => $query
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->with(['children' => fn ($nested) => $nested->active()->orderBy('sort_order')->orderBy('name')])])
            ->get()
            ->sortBy(fn (Category $category): int|false => array_search($category->id, $ids, true))
            ->values();

        return $categories;
    }

    public static function forgetNav(): void
    {
        Cache::forget(self::NAV_CATEGORIES);
        Cache::forget('storefront.nav_categories');
    }
}
