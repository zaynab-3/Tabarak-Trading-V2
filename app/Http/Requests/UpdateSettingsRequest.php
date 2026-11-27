<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:100'],
            'catalogue_intro' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'company_legal_name' => ['required', 'string', 'max:180'],
            'company_address_line_1' => ['required', 'string', 'max:255'],
            'company_address_line_2' => ['required', 'string', 'max:255'],
            'company_phone' => ['required', 'string', 'max:40'],
            'company_email' => ['required', 'email', 'max:255'],
            'invoice_policy' => ['required', 'string', 'max:3000'],
        ];
    }
}
