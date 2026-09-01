<?php

namespace App\Actions\Products;

use App\DTOs\ProductData;
use App\Models\Product;
use App\Services\Products\UniqueSlugGenerator;
use Illuminate\Support\Facades\DB;

class CreateProduct
{
    public function __construct(private readonly UniqueSlugGenerator $slugs) {}

    public function handle(ProductData $data): Product
    {
        return DB::transaction(function () use ($data) {
            return Product::query()->create([
                ...$data->toAttributes(),
                'slug' => $this->slugs->generate(Product::class, $data->name),
            ]);
        });
    }
}
