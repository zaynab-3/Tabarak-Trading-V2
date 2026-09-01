<?php

namespace App\Services\Media;

use App\DTOs\OptimizedImageData;
use GdImage;
use RuntimeException;

class ImageOptimizer
{
    /** @var array<int, string> */
    private const SUPPORTED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function optimize(string $sourcePath, ?string $mimeType = null, ?string $extension = null): OptimizedImageData
    {
        $dimensions = @getimagesize($sourcePath) ?: [null, null];
        $mimeType = $mimeType ?: ($dimensions['mime'] ?? 'application/octet-stream');
        $extension = strtolower($extension ?: $this->extensionFor($mimeType));
        $original = new OptimizedImageData(
            path: $sourcePath,
            mimeType: $mimeType,
            extension: $extension,
            size: (int) filesize($sourcePath),
            width: isset($dimensions[0]) ? (int) $dimensions[0] : null,
            height: isset($dimensions[1]) ? (int) $dimensions[1] : null,
            temporary: false,
            processed: false,
            optimized: false,
        );

        if (! config('media.optimization.enabled')
            || ! extension_loaded('gd')
            || ! function_exists('imagewebp')
            || ! in_array($mimeType, self::SUPPORTED_MIME_TYPES, true)) {
            return $original;
        }

        $contents = @file_get_contents($sourcePath);
        $source = $contents !== false ? @imagecreatefromstring($contents) : false;
        if (! $source instanceof GdImage) {
            return $original;
        }

        $source = $this->orient($source, $sourcePath, $mimeType);
        [$targetWidth, $targetHeight] = $this->targetDimensions(imagesx($source), imagesy($source));
        $resized = $targetWidth !== imagesx($source) || $targetHeight !== imagesy($source);
        $output = $resized ? $this->resize($source, $targetWidth, $targetHeight) : $source;

        $temporaryPath = tempnam(sys_get_temp_dir(), 'tabarak-image-');
        if ($temporaryPath === false) {
            if ($output !== $source) imagedestroy($output);
            imagedestroy($source);
            throw new RuntimeException('A temporary image file could not be created.');
        }

        $quality = max(75, min(95, (int) config('media.optimization.webp_quality', 88)));
        $encoded = imagewebp($output, $temporaryPath, $quality);
        if ($output !== $source) imagedestroy($output);
        imagedestroy($source);

        if (! $encoded || ! is_file($temporaryPath)) {
            @unlink($temporaryPath);

            return $original;
        }

        $optimizedSize = (int) filesize($temporaryPath);
        if (! $resized && $optimizedSize >= $original->size) {
            @unlink($temporaryPath);

            return new OptimizedImageData(
                path: $original->path,
                mimeType: $original->mimeType,
                extension: $original->extension,
                size: $original->size,
                width: $original->width,
                height: $original->height,
                temporary: false,
                processed: true,
                optimized: false,
            );
        }

        return new OptimizedImageData(
            path: $temporaryPath,
            mimeType: 'image/webp',
            extension: 'webp',
            size: $optimizedSize,
            width: $targetWidth,
            height: $targetHeight,
            temporary: true,
            processed: true,
            optimized: true,
        );
    }

    /** @return array{0: int, 1: int} */
    private function targetDimensions(int $width, int $height): array
    {
        $maximum = max(800, (int) config('media.optimization.max_dimension', 1800));
        if (max($width, $height) <= $maximum) {
            return [$width, $height];
        }

        $scale = $maximum / max($width, $height);

        return [max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale))];
    }

    private function resize(GdImage $source, int $width, int $height): GdImage
    {
        $target = imagecreatetruecolor($width, $height);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

        return $target;
    }

    private function orient(GdImage $image, string $path, string $mimeType): GdImage
    {
        if ($mimeType !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($path)['Orientation'] ?? 1;
        $angle = match ((int) $orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if (! $rotated instanceof GdImage) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function extensionFor(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };
    }
}
