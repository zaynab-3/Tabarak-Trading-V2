<?php

namespace App\Http\Requests\Orders;

use App\Enums\OrderPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderInvoiceDetailsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'purchase_order_number' => $this->cleanNullableString($this->input('purchase_order_number')),
            'billing_address' => $this->cleanNullableString($this->input('billing_address')),
            'shipping_address' => $this->cleanNullableString($this->input('shipping_address')),
            'check_number' => $this->cleanNullableString($this->input('check_number')),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'purchase_order_number' => ['nullable', 'string', 'max:80'],
            'billing_address' => ['nullable', 'string', 'max:600'],
            'shipping_address' => ['nullable', 'string', 'max:600'],
            'delivery_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', Rule::enum(OrderPaymentMethod::class)],
            'check_number' => ['nullable', 'string', 'max:80'],
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
