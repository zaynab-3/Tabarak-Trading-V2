<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Imports\DeleteImportItem;
use App\Enums\ImportItemStatus;
use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\ImportItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ImportItemController extends Controller
{
    public function destroy(
        ImportBatch $importBatch,
        ImportItem $importItem,
        DeleteImportItem $action,
    ): RedirectResponse {
        Gate::authorize('update', $importBatch);

        abort_unless($importItem->import_batch_id === $importBatch->id, 404);
        abort_if(
            $importItem->approved_product_id !== null
                || ! in_array($importItem->status, [ImportItemStatus::Review, ImportItemStatus::Failed], true),
            409,
            'Only unpublished review items can be deleted.',
        );

        $action->handle($importItem);

        return back()->with('success', 'Import item deleted.');
    }
}
