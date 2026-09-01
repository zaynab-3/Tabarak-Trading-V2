<?php

namespace App\Services\Imports;

use App\DTOs\ProductImageAnalysisResult;
use App\Models\Media;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;

class OpenAiProductImageAnalyzer implements ProductImageAnalyzerInterface
{
    public function analyze(Media $image): ProductImageAnalysisResult
    {
        $apiKey = trim((string) config('imports.openai.api_key'));

        if ($apiKey === '') {
            throw new RuntimeException('The product image analyzer is not configured.');
        }

        $contents = Storage::disk($image->disk)->get($image->path);

        if ($contents === '') {
            throw new RuntimeException('The product image is empty.');
        }

        $response = Http::baseUrl(rtrim((string) config('imports.openai.base_url'), '/'))
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('imports.openai.timeout'))
            ->retry(2, 750, throw: false)
            ->post('/responses', $this->payload($image, $contents));

        if ($response->failed()) {
            throw new RuntimeException('The image analysis provider request failed.');
        }

        $analysis = $this->decodeAnalysis($response);

        return new ProductImageAnalysisResult(
            name: $this->nullableString($analysis['name'] ?? null),
            brand: $this->nullableString($analysis['brand'] ?? null),
            category: $this->nullableString($analysis['category'] ?? null),
            weight: $this->nullableString($analysis['weight'] ?? null),
            metadata: is_array($analysis['metadata'] ?? null) ? $analysis['metadata'] : [],
            confidence: is_numeric($analysis['confidence'] ?? null) ? (float) $analysis['confidence'] : null,
            warnings: array_values(array_filter(
                is_array($analysis['warnings'] ?? null) ? $analysis['warnings'] : [],
                fn (mixed $warning) => is_string($warning) && trim($warning) !== '',
            )),
            providerMetadata: [
                'provider' => 'openai',
                'model' => $response->json('model'),
                'response_id' => $response->json('id'),
                'usage' => $response->json('usage'),
            ],
        );
    }

    /** @return array<string, mixed> */
    private function payload(Media $image, string $contents): array
    {
        return [
            'model' => (string) config('imports.openai.model'),
            'store' => false,
            'input' => [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => implode(' ', [
                            'Analyze this single wholesale food product image.',
                            'Read the visible package label carefully and identify the exact product name.',
                            'Also extract brand, category, net weight, visible SKU/barcode text, flavor, packaging, pack quantity, and useful label text.',
                            'Use null when a value is not visible or uncertain; do not invent details.',
                            'The result is only an admin draft and will be reviewed by a human.',
                        ]),
                    ],
                    [
                        'type' => 'input_image',
                        'image_url' => sprintf(
                            'data:%s;base64,%s',
                            $image->mime_type,
                            base64_encode($contents),
                        ),
                        'detail' => (string) config('imports.openai.detail'),
                    ],
                ],
            ]],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'product_image_analysis',
                    'strict' => true,
                    'schema' => $this->schema(),
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        $nullableString = ['type' => ['string', 'null']];

        return [
            'type' => 'object',
            'properties' => [
                'name' => $nullableString,
                'brand' => $nullableString,
                'category' => $nullableString,
                'weight' => $nullableString,
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

    /** @return array<string, mixed> */
    private function decodeAnalysis(Response $response): array
    {
        $outputText = collect($response->json('output', []))
            ->filter(fn (mixed $item) => is_array($item) && ($item['type'] ?? null) === 'message')
            ->flatMap(fn (array $item) => $item['content'] ?? [])
            ->first(fn (mixed $content) => is_array($content) && ($content['type'] ?? null) === 'output_text');

        if (! is_array($outputText) || ! is_string($outputText['text'] ?? null)) {
            throw new RuntimeException('The image analysis provider returned no structured result.');
        }

        try {
            $analysis = json_decode($outputText['text'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The image analysis provider returned invalid structured data.', previous: $exception);
        }

        if (! is_array($analysis)) {
            throw new RuntimeException('The image analysis provider returned an invalid result.');
        }

        return $analysis;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
