<?php

namespace App\Services\Imports;

use App\DTOs\ProductData;
use App\Enums\ProductStatus;
use App\Models\ImportItem;
use App\Models\Product;
use Illuminate\Support\Str;

class ImportProductDataFactory
{
    public function __construct(private readonly ImportTaxonomyResolver $taxonomy) {}

    /** @param array<string, mixed> $overrides */
    public function make(ImportItem $item, array $overrides = []): ProductData
    {
        $metadata = $item->suggested_metadata ?? [];
        [$weightValue, $weightUnit] = $this->weight($item->suggested_weight);
        $packQuantity = array_key_exists('pack_quantity', $overrides)
            ? $overrides['pack_quantity']
            : $this->integer($metadata['pack_quantity'] ?? null);
        $sku = $this->availableSku($metadata['sku'] ?? null);

        return ProductData::fromArray([
            'name' => $overrides['name'] ?? $item->suggested_name,
            'brand_id' => $this->taxonomy->brandId($item->suggested_brand),
            'category_id' => array_key_exists('category_id', $overrides)
                ? (filled($overrides['category_id']) ? (int) $overrides['category_id'] : null)
                : $this->taxonomy->categoryId($item->suggested_category),
            'sku' => $sku,
            'short_description' => filled($metadata['description'] ?? null)
                ? Str::limit(trim((string) $metadata['description']), 500, '')
                : null,
            'description' => filled($metadata['detected_text'] ?? null)
                ? "Visible package text: ".trim((string) $metadata['detected_text'])
                : null,
            'weight_value' => $weightValue,
            'weight_unit' => $weightUnit,
            'pack_quantity' => $packQuantity,
            'allows_open_quantity' => (bool) ($overrides['allows_open_quantity'] ?? false),
            'unit_label' => $packQuantity ? 'case' : null,
            'status' => ProductStatus::Published->value,
            'is_featured' => false,
        ]);
    }

    /** @return array{0: float|null, 1: string|null} */
    private function weight(?string $weight): array
    {
        if (! preg_match('/(\d+(?:[.,]\d+)?)\s*(kg|g|ml|l|oz)\b/i', (string) $weight, $matches)) {
            return [null, null];
        }

        return [(float) str_replace(',', '.', $matches[1]), strtolower($matches[2])];
    }

    private function integer(mixed $value): ?int
    {
        return preg_match('/\d+/', (string) $value, $matches) ? (int) $matches[0] : null;
    }

    private function availableSku(mixed $value): ?string
    {
        $sku = trim((string) $value);

        return $sku !== '' && ! Product::query()->where('sku', $sku)->exists() ? $sku : null;
    }
}
