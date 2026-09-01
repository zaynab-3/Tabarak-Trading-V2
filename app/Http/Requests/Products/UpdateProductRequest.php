<?php

namespace App\Http\Requests\Products;

use App\Http\Requests\Products\Concerns\ProductRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    use ProductRules;

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return $this->productRules($this->route('product')?->id);
    }
}
