<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Services\Admin\BrandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $brands) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Brand::class);

        $brands = Brand::query()
            ->withCount('products')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)->orWhere('slug', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.brands.index', [
            'brands' => $brands,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Brand::class);

        return view('admin.brands.create');
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $this->authorize('create', Brand::class);

        $this->brands->create($request->catalogData(), $request->file('logo'));

        return redirect()->route('admin.brands.index')->with('status', 'Marque créée.');
    }

    public function edit(Brand $brand): View
    {
        $this->authorize('update', $brand);

        return view('admin.brands.edit', [
            'brand' => $brand,
        ]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->authorize('update', $brand);

        $this->brands->update($brand, $request->catalogData(), $request->file('logo'));

        return redirect()->route('admin.brands.index')->with('status', 'Marque mise à jour.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('delete', $brand);

        $this->brands->delete($brand);

        return redirect()->route('admin.brands.index')->with('status', 'Marque supprimée.');
    }
}
