<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniqueSku
{
    public static function make(?string $value, ?int $ignoreProductId = null, ?int $ignoreVariantId = null): string
    {
        $sku = strtoupper((string) preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', trim((string) $value))));
        $sku = substr($sku, 0, 48);

        if ($sku === '') {
            $sku = 'OVL-'.strtoupper(Str::random(6));
        }

        $original = $sku;
        $suffix = 1;

        while (self::exists($sku, $ignoreProductId, $ignoreVariantId)) {
            $sku = $original.'-'.$suffix;
            $suffix++;
        }

        return $sku;
    }

    private static function exists(string $sku, ?int $ignoreProductId, ?int $ignoreVariantId): bool
    {
        $productExists = DB::table('products')
            ->where('sku', $sku)
            ->when($ignoreProductId, fn ($query) => $query->where('id', '!=', $ignoreProductId))
            ->exists();

        $variantExists = DB::table('product_variants')
            ->where('sku', $sku)
            ->when($ignoreVariantId, fn ($query) => $query->where('id', '!=', $ignoreVariantId))
            ->exists();

        return $productExists || $variantExists;
    }
}
