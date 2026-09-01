<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Media\StoreMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Media::class);

        return Inertia::render('Admin/Media/Index', [
            'media' => Media::query()->withCount(['productImages', 'importItems'])->latest()->paginate(24),
        ]);
    }

    public function store(StoreMediaRequest $request, StoreMedia $action): RedirectResponse
    {
        Gate::authorize('create', Media::class);
        foreach ($request->file('images') as $image) {
            $action->handle($image, $request->string('alt_text')->toString() ?: null);
        }

        return back()->with('success', 'Images added to the media library.');
    }

    public function destroy(Media $medium): RedirectResponse
    {
        Gate::authorize('delete', $medium);
        $inUse = $medium->productImages()->exists()
            || $medium->importItems()->exists()
            || Category::query()->where('image_id', $medium->id)->exists()
            || Brand::query()->where('logo_image_id', $medium->id)->exists();
        abort_if($inUse, 422, 'This image is currently in use.');
        Storage::disk($medium->disk)->delete($medium->path);
        $medium->delete();

        return back()->with('success', 'Image deleted.');
    }
}
