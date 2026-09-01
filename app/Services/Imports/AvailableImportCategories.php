<?php

namespace App\Services\Imports;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class AvailableImportCategories
{
    /** @return Collection<int, Category> */
    public function all(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function match(?string $name): ?Category
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        return Category::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->first(['id', 'name']);
    }
}
