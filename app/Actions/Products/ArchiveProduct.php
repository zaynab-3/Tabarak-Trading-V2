<?php

namespace App\Actions\Products;

use App\Enums\ProductStatus;
use App\Models\Product;

class ArchiveProduct
{
    public function handle(Product $product): void
    {
        $product->update(['status' => ProductStatus::Archived, 'published_at' => null]);
    }
}
