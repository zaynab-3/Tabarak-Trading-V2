<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DeleteProduct
{
    public function handle(Product $product): void
    {
        DB::transaction(fn () => $product->delete());
    }
}
