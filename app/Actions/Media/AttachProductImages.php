<?php

namespace App\Actions\Media;

use App\Models\Product;
use Illuminate\Http\UploadedFile;

class AttachProductImages
{
    public function __construct(private readonly StoreMedia $storeMedia) {}

    /** @param array<int, UploadedFile> $images */
    public function handle(Product $product, array $images): void
    {
        $nextOrder = ((int) $product->images()->max('sort_order')) + 1;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($images as $image) {
            $media = $this->storeMedia->handle($image, $product->name);
            $product->images()->firstOrCreate(
                ['media_id' => $media->id],
                ['sort_order' => $nextOrder++, 'is_primary' => ! $hasPrimary],
            );
            $hasPrimary = true;
        }
    }
}
