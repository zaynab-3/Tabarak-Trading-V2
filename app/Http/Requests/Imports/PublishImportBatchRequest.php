<?php

namespace App\Http\Requests\Imports;

use Illuminate\Foundation\Http\FormRequest;

class PublishImportBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        $rules = [
            'allows_open_quantity' => ['required_without:items', 'boolean'],
            'items' => ['sometimes', 'required', 'array', 'min:1', 'max:250'],
            'items.*.id' => ['required', 'integer', 'distinct'],
        ];

        foreach ((new PublishImportItemRequest)->rules() as $field => $fieldRules) {
            $rules['items.*.'.$field] = array_map(
                fn ($rule) => is_string($rule) && str_starts_with($rule, 'required_with:')
                    ? 'required_with:items.*.'.substr($rule, strlen('required_with:'))
                    : $rule,
                $fieldRules,
            );
        }

        $rules['items.*.unit_price'] = ['nullable', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'];
        $rules['items.*.sku'][] = 'distinct:ignore_case';

        return $rules;
    }
}
