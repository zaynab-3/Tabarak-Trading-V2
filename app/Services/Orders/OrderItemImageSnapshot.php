<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderItemImageSnapshot
{
    /** @return array<string, string|null> */
    public function store(Product $product, string $orderToken): array
    {
        $media = $product->primaryImage?->media;
        if (! $media || ! Storage::disk($media->disk)->exists($media->path)) {
            return $this->emptySnapshot();
        }

        $extension = strtolower($media->extension ?: pathinfo($media->path, PATHINFO_EXTENSION) ?: 'bin');
        $path = 'order-invoices/'.$orderToken.'/'.Str::uuid().'.'.$extension;
        $stored = Storage::disk('local')->put($path, Storage::disk($media->disk)->get($media->path));

        if (! $stored) {
            return $this->emptySnapshot();
        }

        return [
            'image_disk' => 'local',
            'image_path' => $path,
            'image_mime_type' => $media->mime_type,
            'image_alt_text' => $media->alt_text ?: $product->name,
        ];
    }

    public function deleteOrderSnapshots(Order $order): void
    {
        Storage::disk('local')->deleteDirectory('order-invoices/'.$order->public_token);
    }

    /** @return array<string, null> */
    private function emptySnapshot(): array
    {
        return [
            'image_disk' => null,
            'image_path' => null,
            'image_mime_type' => null,
            'image_alt_text' => null,
        ];
    }
}
