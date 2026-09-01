<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class OrderItemImageController extends Controller
{
    public function __invoke(Order $order, OrderItem $orderItem): Response
    {
        abort_unless($orderItem->order_id === $order->id, 404);
        abort_unless($orderItem->image_disk && $orderItem->image_path, 404);

        $disk = Storage::disk($orderItem->image_disk);
        abort_unless($disk->exists($orderItem->image_path), 404);

        return response($disk->get($orderItem->image_path), 200, [
            'Content-Type' => $orderItem->image_mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
