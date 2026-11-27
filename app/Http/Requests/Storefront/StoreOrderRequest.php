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
            'purchase_order_number' => $this->cleanNullableString($this->input('purchase_order_number')),
            'billing_address' => $this->cleanNullableString($this->input('billing_address')),
            'shipping_address' => $this->cleanNullableString($this->input('shipping_address')),
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
            'purchase_order_number' => ['nullable', 'string', 'max:80'],
            'billing_address' => ['required', 'string', 'min:5', 'max:600'],
            'shipping_address' => ['nullable', 'string', 'max:600'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Enter the shop or owner name.',
            'customer_phone.regex' => 'Enter a valid U.S. number such as +1 (202) 222 2222.',
            'billing_address.required' => 'Enter the billing address.',
        ];
    }

    private function cleanNullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
