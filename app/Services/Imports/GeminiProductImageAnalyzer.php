<?php

namespace App\Services\Imports;

use App\DTOs\ProductImageAnalysisResult;
use App\Models\Media;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class GeminiProductImageAnalyzer implements ProductImageAnalyzerInterface
{
    public function __construct(private readonly ProductImageAnalysisDefinition $definition) {}

    public function analyze(Media $image): ProductImageAnalysisResult
    {
        $apiKey = trim((string) config('imports.gemini.api_key'));

        if ($apiKey === '') {
            throw new RuntimeException('The Gemini product image analyzer is not configured.');
        }

        $contents = Storage::disk($image->disk)->get($image->path);

        if ($contents === '') {
            throw new RuntimeException('The product image is empty.');
        }

        $model = (string) config('imports.gemini.model');
        $response = Http::baseUrl(rtrim((string) config('imports.gemini.base_url'), '/'))
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('imports.gemini.timeout'))
            ->retry(2, 750, throw: false)
            ->post('/models/'.rawurlencode($model).':generateContent', [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [
                        ['text' => $this->definition->prompt()],
                        [
                            'inline_data' => [
                                'mime_type' => $image->mime_type,
                                'data' => base64_encode($contents),
                            ],
                        ],
                    ],
                ]],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                    'responseJsonSchema' => $this->definition->schema(),
                ],
            ]);

        if ($response->failed()) {
            Log::warning('Gemini image analysis request rejected', [
                'status' => $response->status(),
                'provider_code' => $response->json('error.status'),
                'provider_message' => Str::limit(
                    strip_tags((string) $response->json('error.message', 'No provider message returned.')),
                    500,
                ),
            ]);

            throw new RuntimeException('The Gemini image analysis request failed.');
        }

        return $this->definition->result($this->decodeAnalysis($response), [
            'provider' => 'gemini',
            'model' => $response->json('modelVersion', $model),
            'response_id' => $response->json('responseId'),
            'usage' => $response->json('usageMetadata'),
        ]);
    }

    /** @return array<string, mixed> */
    private function decodeAnalysis(Response $response): array
    {
        $output = collect($response->json('candidates.0.content.parts', []))
            ->first(fn (mixed $part) => is_array($part) && is_string($part['text'] ?? null));

        if (! is_array($output)) {
            throw new RuntimeException('Gemini returned no structured product result.');
        }

        try {
            $analysis = json_decode($output['text'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Gemini returned invalid structured product data.', previous: $exception);
        }

        if (! is_array($analysis)) {
            throw new RuntimeException('Gemini returned an invalid product result.');
        }

        return $analysis;
    }
}
