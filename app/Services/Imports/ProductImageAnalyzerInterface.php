<?php

namespace App\Services\Imports;

use App\DTOs\ProductImageAnalysisResult;
use App\Models\Media;

interface ProductImageAnalyzerInterface
{
    public function analyze(Media $image): ProductImageAnalysisResult;
}
