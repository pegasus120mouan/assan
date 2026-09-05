<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Storefront\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(Request $request): View
    {
        $term = is_string($request->query('q'))
            ? mb_substr(trim($request->query('q')), 0, 120)
            : '';

        return view('storefront.catalog.search', [
            'products' => $this->catalog->paginate($request),
            'term' => $term,
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $term = is_string($request->query('q'))
            ? mb_substr(trim($request->query('q')), 0, 120)
            : '';

        if (mb_strlen($term) < 2) {
            return response()->json(['products' => [], 'categories' => []]);
        }

        $products = $this->catalog->searchSuggestions($term)->map(fn ($product) => [
            'name' => $product->name,
            'price' => format_price($product->selling_price),
            'url' => route('catalog.product', $product),
        ]);

        $categories = Category::query()
            ->active()
            ->where('name', 'like', '%'.addcslashes($term, '%_\\').'%')
            ->orderBy('name')
            ->limit(4)
            ->get()
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'url' => route('catalog.category', $category),
            ]);

        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
