<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WishlistController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->published()
            ->whereIn('id', $request->user()->wishlists()->pluck('product_id'))
            ->with(['primaryImage', 'category', 'brand', 'variants'])
            ->latest()
            ->paginate(20);

        return ProductResource::collection($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $item = Wishlist::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $data['product_id'],
        ]);

        return response()->json([
            'data' => [
                'id' => $item->id,
                'product_id' => $item->product_id,
            ],
        ], $item->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $request->user()->wishlists()->where('product_id', $product->id)->delete();

        return response()->json(['message' => 'Retiré des favoris.']);
    }
}
