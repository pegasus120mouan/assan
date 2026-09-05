<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Storefront\CatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(Request $request): View
    {
        return view('storefront.catalog.index', [
            'products' => $this->catalog->paginate($request),
            'categories' => $this->catalog->activeCategories(),
            'brands' => $this->catalog->activeBrands(),
            'currentCategory' => null,
            'filters' => $request->only(['q', 'category', 'brand', 'min_price', 'max_price', 'in_stock', 'on_sale', 'is_new', 'best_seller', 'sort']),
        ]);
    }

    public function category(Request $request, Category $category): View
    {
        abort_unless($category->isActive(), 404);
        $category->load(['children' => fn ($query) => $query->active()->orderBy('sort_order')->orderBy('name')]);

        return view('storefront.catalog.index', [
            'products' => $this->catalog->paginate($request, $category),
            'categories' => $this->catalog->activeCategories(),
            'brands' => $this->catalog->activeBrands(),
            'currentCategory' => $category,
            'filters' => $request->only(['q', 'category', 'brand', 'min_price', 'max_price', 'in_stock', 'on_sale', 'is_new', 'best_seller', 'sort']),
        ]);
    }
}
