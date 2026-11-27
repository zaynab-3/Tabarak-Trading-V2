<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use App\Support\CompanyPolicy;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'site_name' => Setting::valueFor('site_name', 'Tabarak Trading'),
                'catalogue_intro' => Setting::valueFor('catalogue_intro', ''),
                'contact_email' => Setting::valueFor('contact_email', 'tabaraktrading73@gmail.com'),
                'company_legal_name' => Setting::valueFor('company_legal_name', 'Tabarak Trading LLC'),
                'company_address_line_1' => Setting::valueFor('company_address_line_1', '14301 Ford Rd'),
                'company_address_line_2' => Setting::valueFor('company_address_line_2', 'Dearborn, MI 48126'),
                'company_phone' => Setting::valueFor('company_phone', '(248) 938-9999'),
                'company_email' => Setting::valueFor('company_email', 'tabaraktrading73@gmail.com'),
                'invoice_policy' => Setting::valueFor('invoice_policy', CompanyPolicy::defaultText()),
            ],
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'string']);
        }

        return back()->with('success', 'Settings saved.');
    }

}