<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\AuditLogger;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Promotion::class);

        $promotions = Promotion::query()
            ->with(['product', 'category'])
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('priority')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.promotions.index', [
            'promotions' => $promotions,
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Promotion::class);

        return view('admin.promotions.create', $this->formData());
    }

    public function store(PromotionRequest $request, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('create', Promotion::class);
        $data = $request->promotionData();
        $data['slug'] = UniqueSlug::make($data['name'], 'promotions');
        $promotion = Promotion::query()->create($data);
        $audit->record('Promotion créée', 'promotions', $promotion, null, $promotion->toArray());

        return redirect()->route('admin.promotions.index')->with('status', 'Promotion créée.');
    }

    public function edit(Promotion $promotion): View
    {
        $this->authorize('update', $promotion);

        return view('admin.promotions.edit', array_merge($this->formData(), [
            'promotion' => $promotion,
        ]));
    }

    public function update(PromotionRequest $request, Promotion $promotion, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('update', $promotion);
        $old = $promotion->toArray();
        $data = $request->promotionData();
        $data['slug'] = UniqueSlug::make($data['name'], 'promotions', $promotion->id);
        $promotion->update($data);
        $audit->record('Promotion modifiée', 'promotions', $promotion, $old, $promotion->fresh()?->toArray());

        return redirect()->route('admin.promotions.index')->with('status', 'Promotion mise à jour.');
    }

    public function destroy(Promotion $promotion, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('delete', $promotion);
        $old = $promotion->toArray();
        $promotion->delete();
        $audit->record('Promotion supprimée', 'promotions', null, $old);

        return redirect()->route('admin.promotions.index')->with('status', 'Promotion supprimée.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
