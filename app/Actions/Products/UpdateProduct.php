<?php

namespace App\Actions\Products;

use App\DTOs\ProductData;
use App\Models\Product;
use App\Services\Products\UniqueSlugGenerator;
use Illuminate\Support\Facades\DB;

class UpdateProduct
{
    public function __construct(private readonly UniqueSlugGenerator $slugs) {}

    public function handle(Product $product, ProductData $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update([
                ...$data->toAttributes(),
                'slug' => $this->slugs->generate(Product::class, $data->name, $product->id),
            ]);

            return $product->refresh();
        });
    }
}
