<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\StoreMedia;
use App\Actions\Media\UpdateMediaMetadata;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Media::class);

        return Inertia::render('Admin/Media/Index', [
            'media' => Media::query()
                ->withCount(['productImages', 'importItems', 'categoryImages', 'brandLogos'])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(24),
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

    public function update(UpdateMediaRequest $request, Media $medium, UpdateMediaMetadata $action): RedirectResponse
    {
        Gate::authorize('update', $medium);
        $action->handle($medium, $request->validated());

        return back()->with('success', 'Image details updated.');
    }

    public function destroy(Media $medium, DeleteMedia $action): RedirectResponse
    {
        Gate::authorize('delete', $medium);
        $action->handle($medium);

        return back()->with('success', 'Image and its catalogue links were deleted.');
    }
}
