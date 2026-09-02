<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderDeletionNotice;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class OrderDeletionNoticeItemImageController extends Controller
{
    public function __invoke(OrderDeletionNotice $orderNotice, int $item): Response
    {
        Gate::authorize('view', $orderNotice);
        $snapshot = collect($orderNotice->items)->firstWhere('id', $item);

        abort_unless(is_array($snapshot) && filled($snapshot['image_path'] ?? null), 404);
        abort_unless(Storage::disk($snapshot['image_disk'])->exists($snapshot['image_path']), 404);

        return Storage::disk($snapshot['image_disk'])->response(
            $snapshot['image_path'],
            null,
            [
                'Content-Type' => $snapshot['image_mime_type'] ?: 'application/octet-stream',
                'Cache-Control' => 'private, max-age=3600',
            ],
        );
    }
}
