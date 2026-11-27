<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\CompanyPolicy;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'Tabarak Trading',
            'catalogue_intro' => 'A dependable wholesale range selected for stores and food businesses.',
            'contact_email' => 'tabaraktrading73@gmail.com',
            'company_legal_name' => 'Tabarak Trading LLC',
            'company_address_line_1' => '14301 Ford Rd',
            'company_address_line_2' => 'Dearborn, MI 48126',
            'company_phone' => '(248) 938-9999',
            'company_email' => 'tabaraktrading73@gmail.com',
            'invoice_policy' => CompanyPolicy::defaultText(),
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'string']);
        }
    }
}
