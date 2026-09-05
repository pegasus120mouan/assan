<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Concerns\HasActiveStatus;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'slug', 'logo', 'description', 'status'])]
class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasActiveStatus, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }
}
