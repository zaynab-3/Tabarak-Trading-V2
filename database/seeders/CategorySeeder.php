<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Nuts', 'Roasted, raw and seasoned nuts for retail and food service.'],
            ['Seeds', 'Everyday seeds in practical wholesale formats.'],
            ['Snacks', 'Savoury snack lines chosen for strong shelf appeal.'],
            ['Coffee', 'Ground, whole-bean and instant coffee selections.'],
            ['Chocolate', 'Chocolate bars, bites and sharing formats.'],
            ['Biscuits', 'Classic biscuits and modern snackable formats.'],
            ['Candy', 'Colourful confectionery for counters and aisles.'],
            ['Beverages', 'Shelf-stable drinks and refreshment essentials.'],
        ];

        foreach ($categories as $index => [$name, $description]) {
            Category::query()->updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => $description,
                'sort_order' => ($index + 1) * 10,
                'is_active' => true,
            ]);
        }
    }
}
