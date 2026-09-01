<?php

namespace App\Services\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Services\Products\UniqueSlugGenerator;

class ImportTaxonomyResolver
{
    public function __construct(private readonly UniqueSlugGenerator $slugs) {}

    public function brandId(?string $name): ?int
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $brand = Brand::query()->whereRaw('LOWER(name) = LOWER(?)', [$name])->first();

        return ($brand ?? Brand::query()->create([
            'name' => $name,
            'slug' => $this->slugs->generate(Brand::class, $name),
            'is_active' => true,
        ]))->id;
    }

    public function categoryId(?string $name): ?int
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $category = Category::query()->whereRaw('LOWER(name) = LOWER(?)', [$name])->first();

        return ($category ?? Category::query()->create([
            'name' => $name,
            'slug' => $this->slugs->generate(Category::class, $name),
            'sort_order' => ((int) Category::query()->max('sort_order')) + 1,
            'is_active' => true,
        ]))->id;
    }
}
