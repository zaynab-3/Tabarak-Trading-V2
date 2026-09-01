<?php

namespace App\Services\Orders;

use App\Models\OrderItem;
use Illuminate\Support\Facades\Storage;

class InvoiceImageDataUri
{
    public function forItem(OrderItem $item): ?string
    {
        if (! $item->image_disk || ! $item->image_path) {
            return null;
        }

        $disk = Storage::disk($item->image_disk);
        if (! $disk->exists($item->image_path)) {
            return null;
        }

        $mimeType = $item->image_mime_type ?: $disk->mimeType($item->image_path) ?: 'application/octet-stream';

        return 'data:'.$mimeType.';base64,'.base64_encode($disk->get($item->image_path));
    }

    public function logo(): ?string
    {
        $path = public_path('icons/apple-touch-icon.png');

        return is_file($path)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($path))
            : null;
    }
}
