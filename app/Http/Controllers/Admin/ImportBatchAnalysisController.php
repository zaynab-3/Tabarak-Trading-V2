<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Imports\ReanalyzeImportBatch;
use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ImportBatchAnalysisController extends Controller
{
    public function store(ImportBatch $importBatch, ReanalyzeImportBatch $action): RedirectResponse
    {
        Gate::authorize('update', $importBatch);
        $queued = $action->handle($importBatch);

        return back()->with(
            'success',
            $queued > 0
                ? "$queued image(s) queued for fresh AI analysis."
                : 'No review items are available for re-analysis.',
        );
    }
}
