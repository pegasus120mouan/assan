<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Storefront\CatalogService;
use App\Services\Storefront\ReviewService;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly CatalogService $catalog,
        private readonly ReviewService $reviews,
    ) {}

    public function show(Product $product): View
    {
        abort_unless($product->isActive(), 404);

        $product->load([
            'images',
            'variants' => fn ($query) => $query->active(),
            'category.parent.parent',
            'brand',
            'reviews' => fn ($query) => $query->approved()->latest()->with('user'),
        ]);

        $product->loadAvg(['reviews' => fn ($query) => $query->approved()], 'rating');
        $product->loadCount(['reviews' => fn ($query) => $query->approved()]);

        $user = auth()->user();

        return view('storefront.catalog.show', [
            'product' => $product,
            'similar' => $this->catalog->similar($product),
            'canReview' => $user ? $this->reviews->canReview($user, $product) : false,
            'existingReview' => $user ? $this->reviews->existing($user, $product) : null,
            ...$this->catalog->neighbours($product),
        ]);
    }
}
