<?php

namespace App\DTOs;

readonly class OptimizedImageData
{
    public function __construct(
        public string $path,
        public string $mimeType,
        public string $extension,
        public int $size,
        public ?int $width,
        public ?int $height,
        public bool $temporary,
        public bool $processed,
        public bool $optimized,
    ) {}

    public function cleanup(): void
    {
        if ($this->temporary && is_file($this->path)) {
            @unlink($this->path);
        }
    }
}
