<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniqueSlug
{
    public static function make(string $value, string $table, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $slug = Str::slug($value) ?: 'item';
        $original = $slug;
        $suffix = 1;

        while (DB::table($table)
            ->where($column, $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $original.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
