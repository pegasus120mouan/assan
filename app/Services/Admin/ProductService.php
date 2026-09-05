<?php

namespace App\Services\Admin;

use App\Enums\Status;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\AuditLogger;
use App\Services\ImageUploader;
use App\Support\UniqueSku;
use App\Support\UniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductService
{
    public function __construct(
        private readonly ImageUploader $images,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @return Builder<Product>
     */
    public function filteredQuery(Request $request): Builder
    {
        $trashed = $request->string('trashed')->toString();

        return Product::query()
            ->when($trashed === 'only', fn (Builder $query) => $query->onlyTrashed())
            ->when($trashed === 'with', fn (Builder $query) => $query->withTrashed())
            ->with(['category', 'brand', 'primaryImage', 'variants'])
            ->withCount(['images', 'variants'])
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term)
                        ->orWhere('slug', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('brand_id'), fn (Builder $query) => $query->where('brand_id', $request->integer('brand_id')))
            ->when($request->boolean('featured'), fn (Builder $query) => $query->where('featured', true))
            ->when($request->string('stock')->toString() === 'in', fn (Builder $query) => $query->inStock())
            ->when($request->string('stock')->toString() === 'out', function (Builder $query): void {
                $query->where(function (Builder $builder): void {
                    $builder->where(function (Builder $withVariants): void {
                        $withVariants->whereHas('variants')
                            ->whereDoesntHave('variants', function (Builder $variants): void {
                                $variants->whereRaw('(stock_quantity - reserved_quantity) > 0');
                            });
                    })->orWhere(function (Builder $withoutVariants): void {
                        $withoutVariants->whereDoesntHave('variants')
                            ->whereRaw('(stock_quantity - reserved_quantity) <= 0');
                    });
                });
            })
            ->when($request->string('stock')->toString() === 'low', function (Builder $query): void {
                $query->whereDoesntHave('variants')
                    ->whereRaw('(stock_quantity - reserved_quantity) > 0')
                    ->whereRaw('(stock_quantity - reserved_quantity) <= low_stock_threshold');
            })
            ->latest();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     * @param  array<int, array<string, mixed>>|null  $variants
     */
    public function create(array $data, array $images = [], ?array $variants = null): Product
    {
        return DB::transaction(function () use ($data, $images, $variants): Product {
            $data['slug'] = UniqueSlug::make($data['slug'] ?: $data['name'], 'products');
            $data['sku'] = UniqueSku::make($data['sku'] ?? null);

            $product = Product::query()->create($data);
            $this->storeImages($product, $images);
            $this->ensurePrimaryImage($product);

            if ($variants !== null) {
                $this->syncVariants($product, $variants);
            }

            $this->audit->record('Produit créé', 'products', $product, null, $product->fresh()?->toArray());

            return $product->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     * @param  array<int, array<string, mixed>>|null  $variants
     * @param  list<int>  $removeImageIds
     */
    public function update(
        Product $product,
        array $data,
        array $images = [],
        ?array $variants = null,
        array $removeImageIds = [],
        ?int $primaryImageId = null,
    ): Product {
        return DB::transaction(function () use ($product, $data, $images, $variants, $removeImageIds, $primaryImageId): Product {
            $old = $product->toArray();
            $data['slug'] = UniqueSlug::make($data['slug'] ?: $data['name'], 'products', $product->id);
            $data['sku'] = UniqueSku::make($data['sku'] ?: $product->sku, $product->id);

            $product->update($data);
            $this->deleteImages($product, $removeImageIds);
            $this->storeImages($product, $images);
            $this->ensurePrimaryImage($product->refresh(), $primaryImageId);

            if ($variants !== null) {
                $this->syncVariants($product, $variants);
            }

            $this->audit->record('Produit modifié', 'products', $product, $old, $product->fresh()?->toArray());

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        $old = $product->toArray();
        $product->delete();

        $this->audit->record('Produit supprimé', 'products', $product, $old);
    }

    public function restore(Product $product): void
    {
        $product->restore();

        $this->audit->record('Produit restauré', 'products', $product, null, $product->fresh()?->toArray());
    }

    public function forceDelete(Product $product): void
    {
        if ($product->orderItems()->exists()) {
            throw ValidationException::withMessages([
                'delete' => 'Impossible de supprimer définitivement un produit présent dans des commandes.',
            ]);
        }

        if ($product->stockMovements()->exists()) {
            throw ValidationException::withMessages([
                'delete' => 'Impossible de supprimer définitivement un produit avec un historique de stock.',
            ]);
        }

        $old = $product->toArray();
        $product->cartItems()->delete();

        $product->images->each(function (ProductImage $image): void {
            $this->images->delete($image->image);
        });

        $product->forceDelete();

        $this->audit->record('Produit supprimé définitivement', 'products', null, $old);
    }

    public function export(Request $request): StreamedResponse
    {
        $products = $this->filteredQuery($request)->with(['category', 'brand'])->get();
        $filename = 'produits-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($products): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'name', 'sku', 'slug', 'category_slug', 'brand_slug', 'selling_price',
                'compare_price', 'stock_quantity', 'status', 'featured', 'short_description',
            ], ';');

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->name,
                    $product->sku,
                    $product->slug,
                    $product->category?->slug,
                    $product->brand?->slug,
                    $product->selling_price,
                    $product->compare_price,
                    $product->stock_quantity,
                    $product->status->value,
                    $product->featured ? '1' : '0',
                    $product->short_description,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{created: int, updated: int, errors: list<string>}
     */
    public function import(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath() ?: $file->getPathname(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'Impossible de lire le fichier CSV.',
            ]);
        }

        $headerLine = fgets($handle);

        if ($headerLine === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'file' => 'Le fichier CSV est vide.',
            ]);
        }

        $delimiter = substr_count($headerLine, ';') > substr_count($headerLine, ',') ? ';' : ',';
        $headers = array_map(
            fn (string $header): string => strtolower(trim($this->stripBom($header))),
            str_getcsv($headerLine, $delimiter) ?: [],
        );

        $created = 0;
        $updated = 0;
        $errors = [];
        $line = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;

            if ($this->isEmptyCsvRow($row)) {
                continue;
            }

            $record = [];

            foreach ($headers as $index => $header) {
                $record[$header] = trim((string) ($row[$index] ?? ''));
            }

            try {
                $wasUpdate = $this->importRow($record);
                $wasUpdate ? $updated++ : $created++;
            } catch (ValidationException $exception) {
                $errors[] = 'Ligne '.$line.' : '.$exception->validator->errors()->first();
            } catch (\Throwable $exception) {
                $errors[] = 'Ligne '.$line.' : '.$exception->getMessage();
            }
        }

        fclose($handle);

        $this->audit->record('Produits importés', 'products', null, null, [
            'created' => $created,
            'updated' => $updated,
            'errors' => count($errors),
        ]);

        return compact('created', 'updated', 'errors');
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storeImages(Product $product, array $files): void
    {
        $sortOrder = (int) $product->images()->max('sort_order');

        foreach ($files as $file) {
            $sortOrder++;
            $product->images()->create([
                'image' => $this->images->store($file, 'products/'.$product->id),
                'alt' => $product->name,
                'sort_order' => $sortOrder,
                'is_primary' => false,
            ]);
        }
    }

    /**
     * @param  list<int>  $ids
     */
    private function deleteImages(Product $product, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $product->images()->whereIn('id', $ids)->get()->each(function (ProductImage $image): void {
            $this->images->delete($image->image);
            $image->delete();
        });
    }

    private function ensurePrimaryImage(Product $product, ?int $primaryImageId = null): void
    {
        $images = $product->images()->orderBy('sort_order')->get();

        if ($images->isEmpty()) {
            return;
        }

        $primary = $primaryImageId
            ? $images->firstWhere('id', $primaryImageId)
            : ($images->firstWhere('is_primary', true) ?: $images->first());

        $product->images()->update(['is_primary' => false]);
        $primary?->update(['is_primary' => true]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     */
    private function syncVariants(Product $product, array $variants): void
    {
        $keepIds = [];

        foreach ($variants as $row) {
            $id = filled($row['id'] ?? null) ? (int) $row['id'] : null;
            $variant = $id ? $product->variants()->whereKey($id)->first() : null;

            $payload = [
                'name' => $row['name'],
                'sku' => UniqueSku::make($row['sku'] ?? null, null, $variant?->id),
                'price' => filled($row['price'] ?? null) ? (int) $row['price'] : null,
                'stock_quantity' => (int) ($row['stock_quantity'] ?? 0),
                'status' => $row['status'] ?? Status::Active,
                'options' => $this->parseOptions($row['options'] ?? null, (string) $row['name']),
            ];

            if ($variant) {
                $variant->update($payload);
            } else {
                $variant = $product->variants()->create($payload);
            }

            $keepIds[] = $variant->id;
        }

        $product->variants()
            ->when($keepIds !== [], fn (Builder $query) => $query->whereNotIn('id', $keepIds))
            ->when($keepIds === [], fn (Builder $query) => $query)
            ->get()
            ->each(function (ProductVariant $variant): void {
                if ($variant->orderItems()->exists()) {
                    throw ValidationException::withMessages([
                        'variants' => 'Impossible de supprimer la variante « '.$variant->name.' » : elle est liée à des commandes.',
                    ]);
                }

                $variant->cartItems()->delete();
                $variant->delete();
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function parseOptions(mixed $value, string $variantName): array
    {
        if (is_array($value)) {
            return $value;
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return ['label' => $variantName];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : ['label' => $raw];
    }

    /**
     * @param  array<string, string>  $row
     */
    private function importRow(array $row): bool
    {
        $name = $row['name'] ?? $row['nom'] ?? '';
        $sku = $row['sku'] ?? '';
        $categorySlug = $row['category_slug'] ?? $row['categorie'] ?? '';
        $status = $row['status'] ?? $row['statut'] ?? Status::Active->value;

        if ($name === '' || ($row['selling_price'] ?? $row['prix'] ?? '') === '') {
            throw ValidationException::withMessages([
                'file' => 'Les colonnes name et selling_price sont obligatoires.',
            ]);
        }

        $category = Category::query()->where('slug', $categorySlug)->first();

        if (! $category) {
            throw ValidationException::withMessages([
                'file' => 'Catégorie introuvable ('.$categorySlug.').',
            ]);
        }

        $brandSlug = $row['brand_slug'] ?? $row['marque'] ?? '';
        $brand = $brandSlug !== '' ? Brand::query()->where('slug', $brandSlug)->first() : null;

        if ($brandSlug !== '' && ! $brand) {
            throw ValidationException::withMessages([
                'file' => 'Marque introuvable ('.$brandSlug.').',
            ]);
        }

        if (! in_array($status, [Status::Active->value, Status::Inactive->value], true)) {
            $status = Status::Active->value;
        }

        $data = [
            'category_id' => $category->id,
            'brand_id' => $brand?->id,
            'name' => $name,
            'slug' => $row['slug'] ?? null,
            'sku' => $sku !== '' ? $sku : null,
            'short_description' => $row['short_description'] ?? $row['description'] ?? null,
            'description' => $row['description'] ?? null,
            'purchase_price' => (int) ($row['purchase_price'] ?? 0),
            'selling_price' => (int) ($row['selling_price'] ?? $row['prix'] ?? 0),
            'compare_price' => filled($row['compare_price'] ?? null) ? (int) $row['compare_price'] : null,
            'cost_price' => filled($row['cost_price'] ?? null) ? (int) $row['cost_price'] : null,
            'stock_quantity' => (int) ($row['stock_quantity'] ?? $row['stock'] ?? 0),
            'low_stock_threshold' => 5,
            'weight' => null,
            'status' => $status,
            'featured' => in_array($row['featured'] ?? '', ['1', 'true', 'oui'], true),
            'is_new' => false,
            'is_best_seller' => false,
            'meta_title' => $name,
            'meta_description' => $row['short_description'] ?? $row['description'] ?? null,
        ];

        $existing = $sku !== ''
            ? Product::query()->withTrashed()->where('sku', $sku)->first()
            : null;

        if ($existing?->trashed()) {
            throw ValidationException::withMessages([
                'file' => 'Le SKU '.$sku.' appartient à un produit dans la corbeille.',
            ]);
        }

        if ($existing) {
            $this->update($existing, $data);

            return true;
        }

        $this->create($data);

        return false;
    }

    private function stripBom(string $value): string
    {
        return str_starts_with($value, "\xEF\xBB\xBF") ? substr($value, 3) : $value;
    }

    /**
     * @param  list<string|null>  $row
     */
    private function isEmptyCsvRow(array $row): bool
    {
        return collect($row)->filter(fn (?string $value): bool => filled(trim((string) $value)))->isEmpty();
    }
}
