<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Imports\ApproveImportItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\PublishImportItemRequest;
use App\Models\ImportBatch;
use App\Models\ImportItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ImportItemPublicationController extends Controller
{
    public function store(
        PublishImportItemRequest $request,
        ImportBatch $importBatch,
        ImportItem $importItem,
        ApproveImportItem $action,
    ): RedirectResponse {
        Gate::authorize('update', $importBatch);
        abort_unless($importItem->import_batch_id === $importBatch->id, 404);
        $product = $action->handle($importItem, $request->validated());

        return back()->with('success', "{$product->name} is now published in the shop.");
    }
}
