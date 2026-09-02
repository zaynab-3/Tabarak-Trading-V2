<?php

namespace App\Http\Requests\Orders;

use App\Enums\OrderDeletionMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'deletion_mode' => ['required', Rule::enum(OrderDeletionMode::class)],
        ];
    }
}
