<?php

namespace App\Services\Products;

use App\Models\Product;

class ProductPresenter
{
    /** @return array<string, mixed> */
    public function summary(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'short_description' => $product->short_description,
            'weight_value' => $product->weight_value,
            'weight_unit' => $product->weight_unit,
            'pack_quantity' => $product->pack_quantity,
            'allows_open_quantity' => $product->allows_open_quantity,
            'stock_quantity' => $product->stock_quantity,
            'tracks_stock' => ! $product->allows_open_quantity && $product->stock_quantity !== null,
            'is_available' => $product->allows_open_quantity || $product->stock_quantity === null || $product->stock_quantity > 0,
            'unit_label' => $product->unit_label,
            'unit_price' => $product->unit_price,
            'currency' => 'USD',
            'status' => $product->status->value,
            'is_featured' => $product->is_featured,
            'updated_at' => $product->updated_at?->toISOString(),
            'brand' => $product->brand ? ['id' => $product->brand->id, 'name' => $product->brand->name, 'slug' => $product->brand->slug] : null,
            'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name, 'slug' => $product->category->slug] : null,
            'primary_image' => $product->primaryImage?->media ? $this->media($product->primaryImage->media) : null,
        ];
    }

    /** @return array<string, mixed> */
    public function detail(Product $product): array
    {
        return [
            ...$this->summary($product),
            'description' => $product->description,
            'images' => $product->images->map(fn ($image) => [
                'id' => $image->id,
                'sort_order' => $image->sort_order,
                'is_primary' => $image->is_primary,
                'media' => $this->media($image->media),
            ])->values(),
            'variants' => $product->variants->map->only(['id', 'name', 'sku', 'weight_value', 'weight_unit', 'pack_quantity', 'is_active'])->values(),
        ];
    }

    /** @return array<string, mixed> */
    private function media($media): array
    {
        return ['id' => $media->id, 'url' => $media->url, 'alt_text' => $media->alt_text, 'width' => $media->width, 'height' => $media->height];
    }
}
