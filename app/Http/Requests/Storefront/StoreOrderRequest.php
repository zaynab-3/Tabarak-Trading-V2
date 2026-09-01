<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $digits = preg_replace('/\D/', '', (string) $this->input('customer_phone')) ?: '';
        $nationalNumber = str_starts_with($digits, '1') ? substr($digits, 1, 10) : substr($digits, 0, 10);

        $this->merge([
            'customer_phone' => '+1'.$nationalNumber,
        ]);
    }

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
            'customer_phone.regex' => 'Enter a valid U.S. number such as +1 (202) 222 2222.',
        ];
    }
}
