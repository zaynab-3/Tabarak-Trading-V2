<?php

namespace App\DTOs;

readonly class ProductImageAnalysisResult
{
    /** @param array<string, mixed> $metadata @param array<int, string> $warnings @param array<string, mixed> $providerMetadata */
    public function __construct(
        public ?string $name = null,
        public ?string $brand = null,
        public ?string $category = null,
        public ?string $weight = null,
        public array $metadata = [],
        public ?float $confidence = null,
        public array $warnings = [],
        public array $providerMetadata = [],
    ) {}
}
