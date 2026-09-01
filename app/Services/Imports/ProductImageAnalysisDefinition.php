<?php

namespace App\Services\Imports;

use App\DTOs\ProductImageAnalysisResult;

class ProductImageAnalysisDefinition
{
    public function __construct(private readonly AvailableImportCategories $categories) {}

    public function prompt(): string
    {
        $availableCategories = $this->categories->all()->pluck('name')->values()->all();

        return implode(' ', [
            'Analyze this single wholesale food product image.',
            'Read the visible package label carefully and identify the exact product name.',
            'Also extract brand, net weight, visible SKU/barcode text, flavor, packaging, pack quantity, and useful label text.',
            'For category, choose exactly one value from this existing category list: '.json_encode($availableCategories, JSON_UNESCAPED_UNICODE).'.',
            'Never invent, rename, broaden, or create a category. Return category as null when no listed category is a confident match.',
            'Use null when a value is not visible or uncertain; do not invent details.',
            'The result is only an admin draft and will be reviewed by a human.',
        ]);
    }

    /** @return array<string, mixed> */
    public function schema(): array
    {
        $nullableString = ['type' => ['string', 'null']];

        return [
            'type' => 'object',
            'properties' => [
                'name' => $nullableString + ['description' => 'Exact product name visible on the package.'],
                'brand' => $nullableString + ['description' => 'Brand visible on the package.'],
                'category' => $nullableString + ['description' => 'Exact category name from the supplied existing list, or null.'],
                'weight' => $nullableString + ['description' => 'Visible net weight including its unit.'],
                'metadata' => [
                    'type' => 'object',
                    'properties' => [
                        'detected_text' => $nullableString,
                        'sku' => $nullableString,
                        'barcode' => $nullableString,
                        'flavor' => $nullableString,
                        'packaging' => $nullableString,
                        'pack_quantity' => $nullableString,
                        'description' => $nullableString,
                    ],
                    'required' => ['detected_text', 'sku', 'barcode', 'flavor', 'packaging', 'pack_quantity', 'description'],
                    'additionalProperties' => false,
                ],
                'confidence' => [
                    'type' => ['number', 'null'],
                    'minimum' => 0,
                    'maximum' => 1,
                    'description' => 'Overall confidence from zero to one.',
                ],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['name', 'brand', 'category', 'weight', 'metadata', 'confidence', 'warnings'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $analysis
     * @param  array<string, mixed>  $providerMetadata
     */
    public function result(array $analysis, array $providerMetadata): ProductImageAnalysisResult
    {
        $name = $this->nullableString($analysis['name'] ?? null);
        $matchedCategory = $this->categories->match($this->nullableString($analysis['category'] ?? null));
        $warnings = array_values(array_filter(
            is_array($analysis['warnings'] ?? null) ? $analysis['warnings'] : [],
            fn (mixed $warning) => is_string($warning) && trim($warning) !== '',
        ));

        if ($name && ! $matchedCategory) {
            $warnings[] = "Product '{$name}' could not be matched confidently to an existing category. Admin selection is required.";
        }

        return new ProductImageAnalysisResult(
            name: $name,
            brand: $this->nullableString($analysis['brand'] ?? null),
            category: $matchedCategory?->name,
            weight: $this->nullableString($analysis['weight'] ?? null),
            metadata: is_array($analysis['metadata'] ?? null) ? $analysis['metadata'] : [],
            confidence: is_numeric($analysis['confidence'] ?? null) ? (float) $analysis['confidence'] : null,
            warnings: array_values(array_unique($warnings)),
            providerMetadata: $providerMetadata,
        );
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
