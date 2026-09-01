<?php

namespace App\Actions\Media;

use App\Models\Media;

class UpdateMediaMetadata
{
    /** @param array{original_name: string, alt_text?: string|null} $data */
    public function handle(Media $media, array $data): Media
    {
        $media->update([
            'original_name' => trim($data['original_name']),
            'alt_text' => filled($data['alt_text'] ?? null) ? trim((string) $data['alt_text']) : null,
        ]);

        return $media->refresh();
    }
}
