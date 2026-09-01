<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function __invoke(Media $medium): StreamedResponse
    {
        abort_unless(Storage::disk($medium->disk)->exists($medium->path), 404);

        $filename = Str::beforeLast($medium->original_name, '.').'.'.$medium->extension;

        return Storage::disk($medium->disk)->response(
            $medium->path,
            $filename,
            [
                'Content-Type' => $medium->mime_type,
                'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400',
            ],
        );
    }
}
