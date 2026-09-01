<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'normalized_name' => Str::lower($name),
            'sku' => fake()->unique()->bothify('TT-####'),
            'short_description' => fake()->sentence(),
            'weight_value' => 500,
            'weight_unit' => 'g',
            'pack_quantity' => 12,
            'unit_label' => 'case',
            'status' => ProductStatus::Published,
            'is_featured' => false,
            'published_at' => now()->subDay(),
        ];
    }
}
