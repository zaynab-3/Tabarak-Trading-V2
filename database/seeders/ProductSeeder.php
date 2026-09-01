<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['Classic Roasted Almonds', 'Nuts', 'Golden Kernel', 500, 'g', 12],
            ['Sea Salt Cashews', 'Nuts', 'Cedar Grove', 350, 'g', 12],
            ['Premium Pistachio Mix', 'Nuts', 'Atlas Pantry', 450, 'g', 10],
            ['Sunflower Seed Pouches', 'Seeds', 'Atlas Pantry', 250, 'g', 24],
            ['Pumpkin Seeds Lightly Salted', 'Seeds', 'Golden Kernel', 300, 'g', 18],
            ['Crunchy Corn Bites', 'Snacks', 'Riviera Treats', 90, 'g', 30],
            ['Mediterranean Snack Mix', 'Snacks', 'Cedar Grove', 200, 'g', 16],
            ['Dark Roast Ground Coffee', 'Coffee', 'Morning Port', 500, 'g', 12],
            ['Classic Instant Coffee', 'Coffee', 'Morning Port', 200, 'g', 12],
            ['Milk Chocolate Wafer Bites', 'Chocolate', 'Riviera Treats', 150, 'g', 20],
            ['Dark Chocolate Almond Squares', 'Chocolate', 'Cedar Grove', 180, 'g', 18],
            ['Butter Tea Biscuits', 'Biscuits', 'Atlas Pantry', 300, 'g', 20],
            ['Cocoa Cream Sandwich Biscuits', 'Biscuits', 'Riviera Treats', 240, 'g', 24],
            ['Fruit Chew Assortment', 'Candy', 'Orchard Lane', 500, 'g', 12],
            ['Mint Drops Display Jar', 'Candy', 'Riviera Treats', 1, 'kg', 6],
            ['Sparkling Lemon Drink', 'Beverages', 'Orchard Lane', 330, 'ml', 24],
            ['Peach Iced Tea', 'Beverages', 'Morning Port', 500, 'ml', 12],
            ['Mixed Nut Sharing Jar', 'Nuts', 'Golden Kernel', 750, 'g', 8],
        ];

        foreach ($products as $index => [$name, $category, $brand, $weight, $unit, $pack]) {
            Product::query()->updateOrCreate(['slug' => Str::slug($name)], [
                'category_id' => Category::query()->where('name', $category)->value('id'),
                'brand_id' => Brand::query()->where('name', $brand)->value('id'),
                'name' => $name,
                'normalized_name' => Str::lower($name),
                'sku' => 'TT-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'short_description' => "Wholesale-ready {$name} with dependable case packing and clear shelf presentation.",
                'description' => "Selected for independent retailers, hospitality teams and food-service buyers. {$name} is packed for straightforward handling, display and repeat ordering.",
                'weight_value' => $weight,
                'weight_unit' => $unit,
                'pack_quantity' => $pack,
                'unit_label' => 'case',
                'status' => ProductStatus::Published,
                'is_featured' => $index < 6,
                'published_at' => now()->subDays(18 - $index),
            ]);
        }
    }
}
