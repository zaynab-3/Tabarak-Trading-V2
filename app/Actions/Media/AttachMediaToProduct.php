<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductImage;

class AttachMediaToProduct
{
    public function handle(Product $product, Media $media): ProductImage
    {
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        return $product->images()->firstOrCreate(
            ['media_id' => $media->id],
            [
                'sort_order' => ((int) $product->images()->max('sort_order')) + 1,
                'is_primary' => ! $hasPrimary,
            ],
        );
    }
}
