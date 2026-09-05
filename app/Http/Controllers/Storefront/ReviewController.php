<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreReviewRequest;
use App\Models\Product;
use App\Services\Storefront\ReviewService;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product, ReviewService $reviews): RedirectResponse
    {
        abort_unless($product->isActive(), 404);

        $reviews->create($request->user(), $product, $request->validated());

        return back()->with('status', 'Merci. Votre avis sera publié après validation.');
    }
}
