<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductQueryService
{
    /** @param array<string, mixed> $filters */
    public function storefront(array $filters): Builder
    {
        return $this->apply(Product::query()->published(), $filters)
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'primaryImage.media']);
    }

    /** @param array<string, mixed> $filters */
    public function admin(array $filters): Builder
    {
        return $this->apply(Product::query(), $filters)
            ->with(['brand:id,name', 'category:id,name', 'primaryImage.media']);
    }

    /** @param array<string, mixed> $filters */
    private function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $term = '%'.addcslashes(trim($search), '%_\\').'%';
                $query->where(function (Builder $query) use ($term) {
                    $query->where('name', 'like', $term)->orWhere('sku', 'like', $term);
                });
            })
            ->when($filters['category'] ?? null, fn (Builder $query, mixed $category) => $query->where('category_id', $category))
            ->when($filters['brand'] ?? null, fn (Builder $query, mixed $brand) => $query->where('brand_id', $brand))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }
}
