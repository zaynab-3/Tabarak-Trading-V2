<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Imports\AddImagesToImportBatch;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\StoreImportBatchImagesRequest;
use App\Models\ImportBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ImportBatchImageController extends Controller
{
    public function store(
        StoreImportBatchImagesRequest $request,
        ImportBatch $importBatch,
        AddImagesToImportBatch $action,
    ): JsonResponse {
        Gate::authorize('update', $importBatch);
        $batch = $action->handle($importBatch, $request->file('images'));

        return response()->json([
            'batch_id' => $batch->id,
            'total_items' => $batch->total_items,
            'processed_items' => $batch->processed_items,
        ]);
    }
}
