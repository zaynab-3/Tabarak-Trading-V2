<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Imports\CreateImportBatch;
use App\Enums\ImportItemStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\StoreImportBatchRequest;
use App\Models\ImportBatch;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
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
            'importConfig' => $this->importConfig(),
        ]);
    }

    public function store(StoreImportBatchRequest $request, CreateImportBatch $action): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', ImportBatch::class);
        $batch = $action->handle(
            $request->user(),
            $request->string('name')->toString() ?: null,
            $request->file('images', []),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'batch_id' => $batch->id,
                'upload_url' => route('admin.imports.images.store', $batch),
                'show_url' => route('admin.imports.show', $batch),
            ], 201);
        }

        return redirect()->route('admin.imports.show', $batch)->with('success', 'Import batch created. Images are queued for review preparation.');
    }

    public function show(ImportBatch $importBatch): Response
    {
        Gate::authorize('view', $importBatch);
        $importBatch->load(['creator:id,name', 'items.media', 'items.approvedProduct:id,name,slug']);

        return Inertia::render('Admin/Imports/Show', [
            'batch' => $importBatch,
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'analyzer' => $this->importConfig()['analyzer'],
            'canReanalyze' => $this->importConfig()['analyzer']['enabled'] && $importBatch->items->contains(
                fn ($item) => in_array($item->status, [ImportItemStatus::Review, ImportItemStatus::Failed], true)
                    && $item->approved_product_id === null,
            ),
        ]);
    }

    /** @return array<string, mixed> */
    private function importConfig(): array
    {
        $driver = (string) config('imports.analyzer');
        $enabled = match ($driver) {
            'gemini' => filled(config('imports.gemini.api_key')),
            'openai' => filled(config('imports.openai.api_key')),
            default => false,
        };
        $provider = match ($driver) {
            'gemini' => 'Google Gemini',
            'openai' => 'OpenAI',
            default => 'Manual review',
        };
        $model = match ($driver) {
            'gemini' => (string) config('imports.gemini.model'),
            'openai' => (string) config('imports.openai.model'),
            default => null,
        };

        return [
            'upload_chunk_size' => (int) config('imports.upload_chunk_size'),
            'max_image_size_mb' => (int) config('imports.max_image_size_kb') / 1024,
            'analyzer' => [
                'driver' => $driver,
                'enabled' => $enabled,
                'provider' => $provider,
                'model' => $enabled ? $model : null,
            ],
        ];
    }
}
