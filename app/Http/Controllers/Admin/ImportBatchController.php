<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Imports\CreateImportBatch;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\StoreImportBatchRequest;
use App\Models\ImportBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ImportBatchController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', ImportBatch::class);

        return Inertia::render('Admin/Imports/Index', [
            'batches' => ImportBatch::query()->with('creator:id,name')->latest()->paginate(15),
        ]);
    }

    public function store(StoreImportBatchRequest $request, CreateImportBatch $action): RedirectResponse
    {
        Gate::authorize('create', ImportBatch::class);
        $batch = $action->handle($request->user(), $request->string('name')->toString() ?: null, $request->file('images'));

        return redirect()->route('admin.imports.show', $batch)->with('success', 'Import batch created. Images are queued for review preparation.');
    }

    public function show(ImportBatch $importBatch): Response
    {
        Gate::authorize('view', $importBatch);
        $importBatch->load(['creator:id,name', 'items.media', 'items.approvedProduct:id,name,slug']);

        return Inertia::render('Admin/Imports/Show', ['batch' => $importBatch]);
    }
}
