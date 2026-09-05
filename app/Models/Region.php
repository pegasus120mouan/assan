<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Concerns\HasActiveStatus;
use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'status', 'sort_order'])]
class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
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

    public function cities(): HasMany
    {
        return $this->hasMany(City::class)->orderBy('sort_order');
    }
}
