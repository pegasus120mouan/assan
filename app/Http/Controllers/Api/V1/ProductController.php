<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Services\Storefront\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'string', 'max:40'],
            'category' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'integer'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
        ]);

        return ProductResource::collection($this->catalog->paginate($request));
    }

    public function show(string $product): ProductResource
    {
        $model = $this->catalog->publishedQuery()
            ->where('slug', $product)
            ->firstOrFail();

        return new ProductResource($model);
    }
}
