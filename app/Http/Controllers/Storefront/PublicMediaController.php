<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function __invoke(Media $medium): StreamedResponse
    {
        abort_unless(Storage::disk($medium->disk)->exists($medium->path), 404);

        return Storage::disk($medium->disk)->response(
            $medium->path,
            $medium->original_name,
            ['Cache-Control' => 'public, max-age=86400'],
        );
    }
}
