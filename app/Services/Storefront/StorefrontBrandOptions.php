<?php

namespace App\Services\Storefront;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StorefrontBrandOptions
{
    /** @return Collection<int, Brand> */
    public function forCategory(int|string|null $categoryId): Collection
    {
        return Brand::query()
            ->where('is_active', true)
            ->whereHas('products', function (Builder $query) use ($categoryId): void {
                $query->published()
                    ->when($categoryId, fn (Builder $query, mixed $category) => $query->where('category_id', $category));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }
}
