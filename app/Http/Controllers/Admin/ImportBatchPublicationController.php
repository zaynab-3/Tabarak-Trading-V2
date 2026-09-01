<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Imports\PublishImportBatch;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\PublishImportBatchRequest;
use App\Models\ImportBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ImportBatchPublicationController extends Controller
{
    public function store(
        PublishImportBatchRequest $request,
        ImportBatch $importBatch,
        PublishImportBatch $action,
    ): RedirectResponse {
        Gate::authorize('update', $importBatch);
        $count = $action->handle($importBatch, $request->validated());

        return back()->with('success', "$count product(s) published to the shop with their images.");
    }
}
