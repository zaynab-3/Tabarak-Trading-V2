<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['Cedar Grove', 'Mediterranean pantry favourites.'],
            ['Golden Kernel', 'Roasted nuts and snack mixes.'],
            ['Atlas Pantry', 'Reliable staples for wholesale buyers.'],
            ['Riviera Treats', 'Contemporary biscuits and confectionery.'],
            ['Morning Port', 'Coffee and beverage essentials.'],
            ['Orchard Lane', 'Fruit-led snacks and refreshing drinks.'],
        ];

        foreach ($brands as [$name, $description]) {
            Brand::query()->updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => $description,
                'is_active' => true,
            ]);
        }
    }
}
