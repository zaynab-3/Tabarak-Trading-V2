<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Media\ReorderMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\ReorderMediaRequest;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MediaOrderController extends Controller
{
    public function update(ReorderMediaRequest $request, ReorderMedia $action): RedirectResponse
    {
        Gate::authorize('update', Media::class);
        $action->handle($request->validated('media_ids'));

        return back()->with('success', 'Media order saved.');
    }
}
