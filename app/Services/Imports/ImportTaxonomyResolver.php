<?php

namespace App\Services\Imports;

use App\Models\Brand;
use App\Services\Products\UniqueSlugGenerator;

class ImportTaxonomyResolver
{
    public function __construct(
        private readonly UniqueSlugGenerator $slugs,
        private readonly AvailableImportCategories $categories,
    ) {}

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
        return $this->categories->match($name)?->id;
    }
}
