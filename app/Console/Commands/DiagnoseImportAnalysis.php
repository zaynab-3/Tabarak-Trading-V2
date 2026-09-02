<?php

namespace App\Console\Commands;

use App\Services\Imports\OcrProductImageAnalyzer;
use Illuminate\Console\Command;

class DiagnoseImportAnalysis extends Command
{
    protected $signature = 'imports:diagnose';

    protected $description = 'Check the configured product-image analysis fallback chain without calling an AI API';

    public function handle(OcrProductImageAnalyzer $ocr): int
    {
        $driver = (string) config('imports.analyzer');
        $primaryConfigured = match ($driver) {
            'gemini' => filled(config('imports.gemini.api_key')),
            'openai' => filled(config('imports.openai.api_key')),
            default => false,
        };
        $ocrAvailable = $ocr->available();
        $queue = (string) config('queue.default');

        $this->components->info('Product image analysis diagnostics');
        $this->line('Primary AI: '.($primaryConfigured ? strtoupper($driver).' configured' : 'not configured'));
        $this->line('Local OCR: '.($ocrAvailable ? 'Tesseract available' : 'not available'));
        $this->line('Queue connection: '.$queue);

        if ($queue !== 'sync') {
            $this->components->warn('A running queue worker is required for analysis jobs.');
        }

        if (! $primaryConfigured && ! $ocrAvailable) {
            $this->components->warn('Images will still upload and become manual-review items.');
        } else {
            $this->components->info('Fallback chain is ready; manual review remains the final fallback.');
        }

        return self::SUCCESS;
    }
}
