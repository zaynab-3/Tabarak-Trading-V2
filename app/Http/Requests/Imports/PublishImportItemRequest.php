<?php

namespace App\Http\Requests\Imports;

use Illuminate\Foundation\Http\FormRequest;

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
            'pack_quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'allows_open_quantity' => ['required', 'boolean'],
        ];
    }
}
