<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use App\Services\Storefront\CatalogService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->catalog->activeCategories());
    }

    public function show(Category $category): CategoryResource
    {
        abort_unless($category->isActive(), 404);

        $category->load(['children' => fn ($query) => $query->active()->orderBy('sort_order')->orderBy('name')]);

        return new CategoryResource($category);
    }
}
