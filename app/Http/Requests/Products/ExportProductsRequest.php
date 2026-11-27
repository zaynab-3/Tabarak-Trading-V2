<?php

namespace App\Http\Requests\Products;

use App\Services\Products\ProductExcelExport;
use Illuminate\Validation\Rule;

class ExportProductsRequest extends ProductIndexRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'scope' => ['required', Rule::in(['all', 'filtered'])],
            'columns' => ['required', 'array', 'min:1', 'max:'.count(ProductExcelExport::columns())],
            'columns.*' => ['required', 'string', 'distinct', Rule::in(array_keys(ProductExcelExport::columns()))],
        ];
    }
}
