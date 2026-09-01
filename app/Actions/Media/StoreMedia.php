<?php

namespace App\Actions\Media;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class StoreMedia
{
    public function handle(UploadedFile $file, ?string $altText = null, string $disk = 'public'): Media
    {
        $checksum = hash_file('sha256', $file->getRealPath());
        $existing = Media::query()->where('checksum', $checksum)->first();

        if ($existing) {
            return $existing;
        }

        $extension = strtolower($file->guessExtension() ?: 'bin');
        $directory = 'catalogue/'.now()->format('Y/m');
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs($directory, $filename, $disk);

        if (! $path) {
            throw new RuntimeException('The image could not be stored.');
        }

        $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];

        return Media::query()->create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => basename($file->getClientOriginalName()),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => $extension,
            'size' => $file->getSize(),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'alt_text' => $altText,
            'checksum' => $checksum,
        ]);
    }
}
