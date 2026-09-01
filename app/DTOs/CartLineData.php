<?php

namespace App\DTOs;

use App\Models\Product;

readonly class CartLineData
{
    public function __construct(
        public Product $product,
        public int $quantity,
        public int $unitPriceCents,
        public int $lineTotalCents,
    ) {}
}
