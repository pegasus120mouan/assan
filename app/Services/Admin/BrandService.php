<?php

namespace App\Services\Admin;

use App\Models\Brand;
use App\Services\AuditLogger;
use App\Services\ImageUploader;
use App\Support\UniqueSlug;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class BrandService
{
    public function __construct(
        private readonly ImageUploader $images,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $logo = null): Brand
    {
        $data['slug'] = UniqueSlug::make($data['slug'] ?: $data['name'], 'brands');

        if ($logo) {
            $data['logo'] = $this->images->store($logo, 'brands');
        }

        $brand = Brand::query()->create($data);

        $this->audit->record('Marque créée', 'brands', $brand, null, $brand->toArray());

        return $brand;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Brand $brand, array $data, ?UploadedFile $logo = null): Brand
    {
        $old = $brand->toArray();
        $data['slug'] = UniqueSlug::make($data['slug'] ?: $data['name'], 'brands', $brand->id);

        if ($logo) {
            $data['logo'] = $this->images->store($logo, 'brands', $brand->logo);
        }

        $brand->update($data);

        $this->audit->record('Marque modifiée', 'brands', $brand, $old, $brand->fresh()?->toArray());

        return $brand->refresh();
    }

    public function delete(Brand $brand): void
    {
        if ($brand->products()->exists()) {
            throw ValidationException::withMessages([
                'delete' => 'Impossible de supprimer une marque associée à des produits.',
            ]);
        }

        $old = $brand->toArray();
        $this->images->delete($brand->logo);
        $brand->delete();

        $this->audit->record('Marque supprimée', 'brands', null, $old);
    }
}
