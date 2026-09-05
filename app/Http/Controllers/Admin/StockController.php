<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockMovementRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\AuditLogger;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class StockController extends Controller
{
    public function __construct(
        private readonly StockService $stock,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StockMovement::class);

        $products = Product::query()
            ->with(['category', 'variants'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)->orWhere('sku', 'like', $term);
                });
            })
            ->when($request->string('stock')->toString() === 'low', function ($query): void {
                $query->whereDoesntHave('variants')
                    ->whereRaw('(stock_quantity - reserved_quantity) > 0')
                    ->whereRaw('(stock_quantity - reserved_quantity) <= low_stock_threshold');
            })
            ->when($request->string('stock')->toString() === 'out', function ($query): void {
                $query->whereDoesntHave('variants')
                    ->whereRaw('(stock_quantity - reserved_quantity) <= 0');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.stock.index', [
            'products' => $products,
            'filters' => $request->only(['q', 'stock']),
        ]);
    }

    public function show(Product $product): View
    {
        $this->authorize('viewAny', StockMovement::class);

        $product->load(['variants', 'category']);

        $movements = $product->stockMovements()
            ->with(['variant', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.stock.show', [
            'product' => $product,
            'movements' => $movements,
        ]);
    }

    public function store(StockMovementRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('create', StockMovement::class);

        $type = StockMovementType::from($request->validated('type'));
        $quantity = (int) $request->validated('quantity');
        $reason = $request->validated('reason');
        $variant = $this->resolveVariant($product, $request->validated('product_variant_id'));

        try {
            $movement = match (true) {
                $type === StockMovementType::Adjustment => $this->stock->adjustStock($product, $quantity, $variant, $reason),
                $type->increasesStock() => $this->stock->increaseStock($product, $quantity, $type, $variant, $reason),
                $type->decreasesStock() => $this->stock->decreaseStock($product, $quantity, $type, $variant, $reason),
                default => throw ValidationException::withMessages([
                    'type' => 'Type de mouvement invalide.',
                ]),
            };
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'quantity' => $exception->getMessage().' Disponible : '.$exception->available.'.',
            ]);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'quantity' => $exception->getMessage(),
            ]);
        }

        $this->audit->record('Mouvement de stock', 'stock', $product, null, $movement->toArray());

        return redirect()
            ->route('admin.stock.show', $product)
            ->with('status', 'Mouvement de stock enregistré.');
    }

    private function resolveVariant(Product $product, mixed $variantId): ?ProductVariant
    {
        if (! filled($variantId)) {
            return null;
        }

        $variant = $product->variants()->whereKey((int) $variantId)->first();

        if (! $variant) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'Cette variante n\'appartient pas au produit.',
            ]);
        }

        return $variant;
    }
}
