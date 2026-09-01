<?php

namespace App\Http\Requests\Products\Concerns;

use App\Enums\ProductStatus;
use App\Enums\WeightUnit;
use Illuminate\Validation\Rule;

trait ProductRules
{
    /** @return array<string, mixed> */
    protected function productRules(?int $productId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'weight_value' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'weight_unit' => ['nullable', Rule::enum(WeightUnit::class)],
            'pack_quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'unit_label' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }
}
