<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:2', 'max:180'],
            'customer_phone' => ['required', 'string', 'regex:/^\+1[2-9]\d{2}[2-9]\d{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Enter the shop or owner name.',
            'customer_phone.regex' => 'Enter a valid U.S. number as +1 followed by 10 digits.',
        ];
    }
}
