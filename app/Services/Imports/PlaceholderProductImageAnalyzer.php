<?php

namespace App\Services\Imports;

use App\DTOs\ProductImageAnalysisResult;
use App\Models\Media;

class PlaceholderProductImageAnalyzer implements ProductImageAnalyzerInterface
{
    public function analyze(Media $image): ProductImageAnalysisResult
    {
        return new ProductImageAnalysisResult(
            warnings: ['AI analysis is not configured. Review this image manually.'],
            providerMetadata: ['provider' => 'placeholder', 'media_id' => $image->id],
        );
    }
}
