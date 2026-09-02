<?php

namespace App\Services\Imports;

use App\DTOs\ProductImageAnalysisResult;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class OcrProductImageAnalyzer implements ProductImageAnalyzerInterface
{
    private ?bool $available = null;

    public function available(): bool
    {
        if ($this->available !== null) {
            return $this->available;
        }

        if (! config('imports.ocr.enabled')) {
            return $this->available = false;
        }

        try {
            $process = new Process([(string) config('imports.ocr.binary', 'tesseract'), '--version']);
            $process->setTimeout(5);
            $process->run();

            return $this->available = $process->isSuccessful();
        } catch (\Throwable) {
            return $this->available = false;
        }
    }

    public function analyze(Media $image): ProductImageAnalysisResult
    {
        if (! $this->available()) {
            throw new RuntimeException('Local OCR is disabled or Tesseract is not installed.');
        }

        $contents = Storage::disk($image->disk)->get($image->path);
        if ($contents === '') {
            throw new RuntimeException('The product image is empty.');
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'tabarak-ocr-');
        if ($temporaryPath === false || file_put_contents($temporaryPath, $contents) === false) {
            throw new RuntimeException('OCR could not prepare a temporary image.');
        }

        try {
            $process = new Process([
                (string) config('imports.ocr.binary', 'tesseract'),
                $temporaryPath,
                'stdout',
                '-l',
                (string) config('imports.ocr.language', 'eng'),
                '--psm',
                '6',
            ]);
            $process->setTimeout((int) config('imports.ocr.timeout', 60));
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('Local OCR could not read the image.');
            }

            $text = Str::of($process->getOutput())->replaceMatches('/\s+/u', ' ')->trim()->toString();
            if ($text === '') {
                throw new RuntimeException('Local OCR found no readable product text.');
            }

            $candidates = $this->candidateLines($process->getOutput());
            $name = Str::limit($candidates->take(3)->implode(' '), 180, '');
            if ($name === '') {
                throw new RuntimeException('Local OCR found text but no usable product name.');
            }

            return new ProductImageAnalysisResult(
                name: $name,
                brand: $candidates->first(),
                metadata: [
                    'detected_text' => Str::limit($text, 2000, ''),
                    'analysis_source' => 'local_ocr',
                ],
                confidence: 0.35,
                warnings: ['AI analysis was unavailable or inconclusive. Local OCR extracted visible text; verify the name, brand, and category before publishing.'],
                providerMetadata: [
                    'provider' => 'tesseract_ocr',
                    'language' => (string) config('imports.ocr.language', 'eng'),
                    'media_id' => $image->id,
                ],
            );
        } finally {
            @unlink($temporaryPath);
        }
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    private function candidateLines(string $text)
    {
        return collect(preg_split('/\R/u', $text) ?: [])
            ->map(fn (string $line) => Str::of($line)->replaceMatches('/\s+/u', ' ')->trim()->toString())
            ->filter(fn (string $line) => mb_strlen($line) >= 2
                && mb_strlen($line) <= 100
                && preg_match('/[a-z]/i', $line)
                && ! preg_match('/^(nutrition|ingredients?|distributed|manufactured|barcode|net\s*w|serving|calories?|www\.|https?:)/i', $line)
                && ! preg_match('/^\W*\d[\d\W]*$/', $line))
            ->values();
    }
}
