<?php

namespace App\Models\Concerns;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Builder;

trait HasActiveStatus
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', Status::Active);
    }

    public function isActive(): bool
    {
        return $this->status === Status::Active;
    }
}
