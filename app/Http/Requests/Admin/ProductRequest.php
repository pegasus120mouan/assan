<?php

namespace App\Http\Requests\Admin;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    protected function prepareForValidation(): void
    {
        [$name, $sku] = $this->normalizeNameAndSku();

        $variants = collect($this->input('variants', []))
            ->map(function (mixed $variant): array {
                $variant = is_array($variant) ? $variant : [];

                return [
                    ...$variant,
                    'id' => filled($variant['id'] ?? null) ? $variant['id'] : null,
                    'price' => filled($variant['price'] ?? null) ? $variant['price'] : null,
                    'sku' => filled($variant['sku'] ?? null) ? $variant['sku'] : null,
                    'stock_quantity' => $variant['stock_quantity'] ?? 0,
                    'status' => $variant['status'] ?? Status::Active->value,
                ];
            })
            ->filter(fn (array $variant): bool => filled($variant['name'] ?? null) || filled($variant['id'] ?? null))
            ->values()
            ->all();

        $this->merge([
            'featured' => $this->boolean('featured'),
            'is_new' => $this->boolean('is_new'),
            'is_best_seller' => $this->boolean('is_best_seller'),
            'brand_id' => $this->input('brand_id') ?: null,
            'compare_price' => $this->input('compare_price') ?: null,
            'cost_price' => $this->input('cost_price') ?: null,
            'purchase_price' => $this->input('purchase_price') ?: 0,
            'weight' => $this->input('weight') ?: null,
            'variants' => $variants,
            'name' => $name !== '' ? $name : null,
            'sku' => $sku,
        ]);
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function normalizeNameAndSku(): array
    {
        $name = trim((string) $this->input('name'));
        $sku = trim((string) $this->input('sku'));

        if ($name === '' && $sku !== '') {
            $name = $sku;
        }

        if ($sku !== '' && mb_strlen($sku) > 64) {
            $sku = '';
        }

        return [$name, $sku !== '' ? $sku : null];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:64'],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string'],
            'purchase_price' => ['nullable', 'integer', 'min:0'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'compare_price' => ['nullable', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(Status::class)],
            'featured' => ['boolean'],
            'is_new' => ['boolean'],
            'is_best_seller' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
            'primary_image_id' => ['nullable', 'integer'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:64'],
            'variants.*.price' => ['nullable', 'integer', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variants.*.status' => ['required', Rule::enum(Status::class)],
            'variants.*.options' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'sku' => 'SKU',
            'short_description' => 'résumé',
            'description' => 'description',
            'selling_price' => 'prix de vente',
            'category_id' => 'catégorie',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productData(): array
    {
        return [
            'category_id' => $this->integer('category_id'),
            'brand_id' => $this->validated('brand_id'),
            'name' => $this->validated('name'),
            'slug' => $this->validated('slug'),
            'sku' => $this->validated('sku'),
            'short_description' => $this->validated('short_description'),
            'description' => $this->validated('description'),
            'purchase_price' => (int) ($this->validated('purchase_price') ?? 0),
            'selling_price' => (int) $this->validated('selling_price'),
            'compare_price' => $this->validated('compare_price'),
            'cost_price' => $this->validated('cost_price'),
            'stock_quantity' => (int) ($this->validated('stock_quantity') ?? 0),
            'low_stock_threshold' => (int) ($this->validated('low_stock_threshold') ?? 5),
            'weight' => $this->validated('weight'),
            'status' => $this->validated('status'),
            'featured' => $this->boolean('featured'),
            'is_new' => $this->boolean('is_new'),
            'is_best_seller' => $this->boolean('is_best_seller'),
            'meta_title' => $this->validated('meta_title') ?: $this->validated('name'),
            'meta_description' => $this->validated('meta_description') ?: $this->validated('short_description'),
        ];
    }

    /**
     * @return array<int, UploadedFile>
     */
    public function uploadedImages(): array
    {
        $files = $this->file('images', []);

        return array_values(array_filter(is_array($files) ? $files : [$files]));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function variantsData(): array
    {
        return $this->validated('variants') ?? [];
    }

    /**
     * @return list<int>
     */
    public function removedImageIds(): array
    {
        return array_map('intval', $this->validated('remove_image_ids') ?? []);
    }

    public function primaryImageId(): ?int
    {
        $id = $this->validated('primary_image_id');

        return $id ? (int) $id : null;
    }
}
