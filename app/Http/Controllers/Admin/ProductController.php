<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductImportRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Admin\ProductService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->products->filteredQuery($request)
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $this->categories(),
            'brands' => $this->brands(),
            'filters' => $request->only(['q', 'status', 'category_id', 'brand_id', 'featured', 'stock', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create', [
            'categories' => $this->categories(),
            'brands' => $this->brands(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $this->products->create(
            $request->productData(),
            $request->uploadedImages(),
            $request->variantsData(),
        );

        return redirect()->route('admin.products.index')->with('status', 'Produit créé.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load(['images', 'variants']);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->categories(),
            'brands' => $this->brands(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->products->update(
            $product,
            $request->productData(),
            $request->uploadedImages(),
            $request->variantsData(),
            $request->removedImageIds(),
            $request->primaryImageId(),
        );

        return redirect()->route('admin.products.index')->with('status', 'Produit mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return redirect()->route('admin.products.index')->with('status', 'Produit déplacé dans la corbeille.');
    }

    public function restore(Product $product): RedirectResponse
    {
        $this->authorize('restore', $product);

        $this->products->restore($product);

        return redirect()->route('admin.products.index')->with('status', 'Produit restauré.');
    }

    public function forceDestroy(Product $product): RedirectResponse
    {
        $this->authorize('forceDelete', $product);

        $this->products->forceDelete($product);

        return redirect()->route('admin.products.index', ['trashed' => 'only'])->with('status', 'Produit supprimé définitivement.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Product::class);

        return $this->products->export($request);
    }

    public function import(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.import');
    }

    public function storeImport(ProductImportRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $result = $this->products->import($request->file('file'));
        $message = $result['created'].' créé(s), '.$result['updated'].' mis à jour.';

        if ($result['errors'] !== []) {
            return redirect()
                ->route('admin.products.import')
                ->with('status', $message)
                ->with('import_errors', $result['errors']);
        }

        return redirect()->route('admin.products.index')->with('status', 'Import terminé : '.$message);
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories()
    {
        return Category::query()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Brand>
     */
    private function brands()
    {
        return Brand::query()->orderBy('name')->get();
    }
}
