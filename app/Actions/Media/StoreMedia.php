<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Services\Media\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StoreMedia
{
    public function __construct(private readonly ImageOptimizer $optimizer) {}

    public function handle(UploadedFile $file, ?string $altText = null, string $disk = 'public'): Media
    {
        $checksum = hash_file('sha256', $file->getRealPath());
        $existing = Media::query()->where('checksum', $checksum)->first();

        if ($existing) {
            return $existing;
        }

        $optimized = $this->optimizer->optimize(
            $file->getRealPath(),
            $file->getMimeType(),
            $file->guessExtension(),
        );

        $path = null;

        try {
            $directory = 'catalogue/'.now()->format('Y/m');
            $path = $directory.'/'.Str::uuid().'.'.$optimized->extension;
            $stream = fopen($optimized->path, 'rb');
            $stored = $stream !== false && Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) fclose($stream);

            if (! $stored) {
                throw new RuntimeException('The image could not be stored.');
            }

            return Media::query()->create([
                'disk' => $disk,
                'path' => $path,
                'original_name' => basename($file->getClientOriginalName()),
                'mime_type' => $optimized->mimeType,
                'extension' => $optimized->extension,
                'size' => $optimized->size,
                'original_size' => $file->getSize(),
                'width' => $optimized->width,
                'height' => $optimized->height,
                'alt_text' => $altText,
                'checksum' => $checksum,
                'optimized_at' => $optimized->processed ? now() : null,
                'sort_order' => ((int) Media::query()->max('sort_order')) + 1,
            ]);
        } catch (\Throwable $exception) {
            if ($path) Storage::disk($disk)->delete($path);
            throw $exception;
        } finally {
            $optimized->cleanup();
        }
    }
}
