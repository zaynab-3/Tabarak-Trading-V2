<?php

namespace App\DTOs;

use App\Enums\ProductStatus;
use Illuminate\Support\Str;

readonly class ProductData
{
    public function __construct(
        public ?int $categoryId,
        public ?int $brandId,
        public string $name,
        public ?string $sku,
        public ?string $description,
        public ?string $shortDescription,
        public ?float $weightValue,
        public ?string $weightUnit,
        public ?int $packQuantity,
        public bool $allowsOpenQuantity,
        public ?string $unitLabel,
        public ProductStatus $status,
        public bool $isFeatured,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            brandId: isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            name: trim((string) $data['name']),
            sku: filled($data['sku'] ?? null) ? trim((string) $data['sku']) : null,
            description: filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            shortDescription: filled($data['short_description'] ?? null) ? trim((string) $data['short_description']) : null,
            weightValue: filled($data['weight_value'] ?? null) ? (float) $data['weight_value'] : null,
            weightUnit: filled($data['weight_unit'] ?? null) ? (string) $data['weight_unit'] : null,
            packQuantity: filled($data['pack_quantity'] ?? null) ? (int) $data['pack_quantity'] : null,
            allowsOpenQuantity: (bool) ($data['allows_open_quantity'] ?? false),
            unitLabel: filled($data['unit_label'] ?? null) ? trim((string) $data['unit_label']) : null,
            status: ProductStatus::from((string) $data['status']),
            isFeatured: (bool) ($data['is_featured'] ?? false),
        );
    }

    /** @return array<string, mixed> */
    public function toAttributes(): array
    {
        return [
            'category_id' => $this->categoryId,
            'brand_id' => $this->brandId,
            'name' => $this->name,
            'normalized_name' => Str::of($this->name)->lower()->squish()->toString(),
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->shortDescription,
            'weight_value' => $this->weightValue,
            'weight_unit' => $this->weightUnit,
            'pack_quantity' => $this->packQuantity,
            'allows_open_quantity' => $this->allowsOpenQuantity,
            'unit_label' => $this->unitLabel,
            'status' => $this->status,
            'is_featured' => $this->isFeatured,
            'published_at' => $this->status === ProductStatus::Published ? now() : null,
        ];
    }
}
