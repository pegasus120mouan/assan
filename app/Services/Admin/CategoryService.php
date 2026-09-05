<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Services\AuditLogger;
use App\Services\ImageUploader;
use App\Support\StorefrontCache;
use App\Support\UniqueSlug;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(
        private readonly ImageUploader $images,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): Category
    {
        $data['slug'] = UniqueSlug::make($data['slug'] ?: $data['name'], 'categories');

        if ($image) {
            $data['image'] = $this->images->store($image, 'categories');
        }

        $category = Category::query()->create($data);

        $this->audit->record('Catégorie créée', 'categories', $category, null, $category->toArray());
        StorefrontCache::forgetNav();

        return $category;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        $old = $category->toArray();
        $data['slug'] = UniqueSlug::make($data['slug'] ?: $data['name'], 'categories', $category->id);

        if ($image) {
            $data['image'] = $this->images->store($image, 'categories', $category->image);
        }

        $category->update($data);

        $this->audit->record('Catégorie modifiée', 'categories', $category, $old, $category->fresh()?->toArray());
        StorefrontCache::forgetNav();

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'delete' => 'Impossible de supprimer une catégorie qui contient des produits.',
            ]);
        }

        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'delete' => 'Supprimez d\'abord les sous-catégories.',
            ]);
        }

        $old = $category->toArray();
        $this->images->delete($category->image);
        $category->delete();

        $this->audit->record('Catégorie supprimée', 'categories', null, $old);
        StorefrontCache::forgetNav();
    }
}
