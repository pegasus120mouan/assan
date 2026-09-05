<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Concerns\HasActiveStatus;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

#[Fillable(['parent_id', 'name', 'slug', 'description', 'image', 'icon', 'status', 'sort_order'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasActiveStatus, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function coverProduct(): HasOne
    {
        return $this->hasOne(Product::class)->ofMany(
            ['id' => 'max'],
            fn (Builder $query) => $query->published(),
        );
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function displayImageUrl(): ?string
    {
        return $this->imageUrl() ?? $this->coverProduct?->coverUrl();
    }

    /**
     * @return Collection<int, self>
     */
    public function breadcrumb(): Collection
    {
        $items = collect();
        $current = $this;

        while ($current) {
            $items->prepend($current);
            $current = $current->parent;
        }

        return $items;
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * @return list<int>
     */
    public function subtreeIds(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->subtreeIds());
        }

        return $ids;
    }
}
