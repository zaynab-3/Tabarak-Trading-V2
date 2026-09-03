<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'custom_unit_price' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
            'reset_custom_price' => ['nullable', 'boolean'],
        ];
    }
}
