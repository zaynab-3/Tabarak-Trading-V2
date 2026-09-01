<?php

namespace App\Support;

use App\Models\Product;

final class ProductPackLabel
{
    public static function for(Product $product): string
    {
        $weight = $product->weight_value && $product->weight_unit
            ? rtrim(rtrim((string) $product->weight_value, '0'), '.').' '.$product->weight_unit
            : null;
        $quantity = $product->allows_open_quantity
            ? 'Open quantity'
            : ($product->pack_quantity ? 'case of '.$product->pack_quantity : null);

        return collect([$weight, $quantity])->filter()->join(' · ') ?: 'Wholesale format';
    }
}
