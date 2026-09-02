<?php

namespace App\Services\Imports;

use App\DTOs\ProductImageAnalysisResult;
use App\Models\Media;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResilientProductImageAnalyzer implements ProductImageAnalyzerInterface
{
    public function __construct(
        private readonly ProductImageAnalyzerInterface $primary,
        private readonly OcrProductImageAnalyzer $ocr,
        private readonly PlaceholderProductImageAnalyzer $manual,
    ) {}

    public function analyze(Media $image): ProductImageAnalysisResult
    {
        try {
            $primaryResult = $this->primary->analyze($image);
            if (filled($primaryResult->name)) {
                return $primaryResult;
            }
        } catch (Throwable $exception) {
            Log::warning('Primary product image analysis failed; trying OCR.', [
                'media_id' => $image->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            return $this->ocr->analyze($image);
        } catch (Throwable $exception) {
            Log::warning('Product image OCR fallback failed; using manual review.', [
                'media_id' => $image->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        $manual = $this->manual->analyze($image);

        return new ProductImageAnalysisResult(
            warnings: ['AI and local OCR could not identify this image. The upload was kept for manual admin review.'],
            providerMetadata: [
                ...$manual->providerMetadata,
                'fallback_chain' => ['primary_ai', 'local_ocr', 'manual_review'],
            ],
        );
    }
}
