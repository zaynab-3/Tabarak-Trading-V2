<?php

namespace App\Http\Requests\Categories;

use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id'), Rule::notIn([$this->route('category')?->id])],
        ];
    }
}
