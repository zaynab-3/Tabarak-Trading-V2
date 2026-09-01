<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'Tabarak Trading',
            'catalogue_intro' => 'A dependable wholesale range, selected for stores and food businesses across Lebanon.',
            'contact_email' => 'admin@tabaraktrading.co',
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'string']);
        }
    }
}
