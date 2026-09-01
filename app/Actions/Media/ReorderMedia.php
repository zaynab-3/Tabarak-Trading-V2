<?php

namespace App\Actions\Media;

use App\Models\Media;
use Illuminate\Support\Facades\DB;

class ReorderMedia
{
    /** @param array<int, int> $mediaIds */
    public function handle(array $mediaIds): void
    {
        $positions = Media::query()
            ->whereKey($mediaIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('sort_order')
            ->sort()
            ->values();

        DB::transaction(function () use ($mediaIds, $positions): void {
            foreach ($mediaIds as $index => $mediaId) {
                Media::query()->whereKey($mediaId)->update([
                    'sort_order' => $positions[$index] ?? $index,
                ]);
            }
        });
    }
}
