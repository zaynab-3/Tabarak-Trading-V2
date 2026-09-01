<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Services\Media\ImageOptimizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class OptimizeStoredMedia
{
    public function __construct(private readonly ImageOptimizer $optimizer) {}

    /** @return array{processed: bool, optimized: bool, bytes_saved: int} */
    public function handle(Media $media): array
    {
        $disk = Storage::disk($media->disk);
        if (! $disk->exists($media->path)) {
            return ['processed' => false, 'optimized' => false, 'bytes_saved' => 0];
        }

        $sourcePath = tempnam(sys_get_temp_dir(), 'tabarak-existing-');
        if ($sourcePath === false) {
            throw new RuntimeException('A temporary image file could not be created.');
        }

        file_put_contents($sourcePath, $disk->get($media->path));
        $optimized = $this->optimizer->optimize($sourcePath, $media->mime_type, $media->extension);

        try {
            if (! $optimized->processed) {
                return ['processed' => false, 'optimized' => false, 'bytes_saved' => 0];
            }

            if (! $optimized->optimized) {
                $media->update([
                    'original_size' => $media->original_size ?: $media->size,
                    'optimized_at' => now(),
                ]);

                return ['processed' => true, 'optimized' => false, 'bytes_saved' => 0];
            }

            $oldPath = $media->path;
            $newPath = trim(pathinfo($oldPath, PATHINFO_DIRNAME), '.').'/'.Str::uuid().'.webp';
            $stream = fopen($optimized->path, 'rb');
            $stored = $stream !== false && $disk->put($newPath, $stream);
            if (is_resource($stream)) fclose($stream);

            if (! $stored) {
                throw new RuntimeException('The optimized image could not be stored.');
            }

            try {
                $originalSize = (int) $media->size;
                $media->update([
                    'path' => $newPath,
                    'mime_type' => $optimized->mimeType,
                    'extension' => $optimized->extension,
                    'size' => $optimized->size,
                    'original_size' => $media->original_size ?: $originalSize,
                    'width' => $optimized->width,
                    'height' => $optimized->height,
                    'optimized_at' => now(),
                ]);
                $disk->delete($oldPath);

                return [
                    'processed' => true,
                    'optimized' => true,
                    'bytes_saved' => max(0, $originalSize - $optimized->size),
                ];
            } catch (Throwable $exception) {
                $disk->delete($newPath);
                throw $exception;
            }
        } finally {
            $optimized->cleanup();
            @unlink($sourcePath);
        }
    }
}
