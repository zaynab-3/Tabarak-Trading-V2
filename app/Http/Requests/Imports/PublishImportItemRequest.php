<?php

namespace App\Http\Requests\Imports;

use App\Enums\WeightUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishImportItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'brand' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'pack_quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'unit_price' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'weight_value' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'required_with:weight_unit'],
            'weight_unit' => ['nullable', Rule::enum(WeightUnit::class), 'required_with:weight_value'],
            'allows_open_quantity' => ['required', 'boolean'],
        ];
    }
}
