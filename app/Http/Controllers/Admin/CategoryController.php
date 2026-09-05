<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\Admin\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::query()
            ->with('parent')
            ->withCount('products')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)->orWhere('slug', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.create', [
            'parents' => $this->parentOptions(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $this->categories->create($request->catalogData(), $request->file('image'));

        return redirect()->route('admin.categories.index')->with('status', 'Catégorie créée.');
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => $this->parentOptions($category->id),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->categories->update($category, $request->catalogData(), $request->file('image'));

        return redirect()->route('admin.categories.index')->with('status', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->categories->delete($category);

        return redirect()->route('admin.categories.index')->with('status', 'Catégorie supprimée.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Category>
     */
    private function parentOptions(?int $exceptId = null)
    {
        return Category::query()
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->orderBy('name')
            ->get();
    }
}
