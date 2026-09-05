<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Storefront\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlists) {}

    public function index(Request $request): View
    {
        $products = Product::query()
            ->published()
            ->whereIn('id', $this->wishlists->productIds($request->user()))
            ->with(['primaryImage', 'category', 'brand', 'variants'])
            ->withAvg(['reviews' => fn ($query) => $query->approved()], 'rating')
            ->latest()
            ->paginate(12);

        return view('storefront.account.wishlist', [
            'products' => $products,
        ]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $added = $this->wishlists->toggle($request->user(), $product);

        return back()->with('status', $added ? 'Ajouté aux favoris.' : 'Retiré des favoris.');
    }
}
