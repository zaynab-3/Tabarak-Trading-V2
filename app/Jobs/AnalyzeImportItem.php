<?php

namespace App\Jobs;

use App\Actions\Imports\RefreshImportBatchProgress;
use App\Enums\ImportItemStatus;
use App\Models\ImportItem;
use App\Services\Imports\ProductImageAnalyzerInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyzeImportItem implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly ImportItem $item) {}

    public function handle(
        ProductImageAnalyzerInterface $analyzer,
        RefreshImportBatchProgress $refreshProgress,
    ): void {
        $this->item->refresh();
        $this->item->update(['status' => ImportItemStatus::Processing]);

        $result = $analyzer->analyze($this->item->media);

        $this->item->update([
            'status' => ImportItemStatus::Review,
            'suggested_name' => $result->name,
            'suggested_brand' => $result->brand,
            'suggested_category' => $result->category,
            'suggested_weight' => $result->weight,
            'suggested_metadata' => $result->metadata,
            'confidence' => $result->confidence,
            'warnings' => $result->warnings,
            'provider_metadata' => $result->providerMetadata,
        ]);

        $refreshProgress->handle($this->item->batch);
    }

    public function failed(Throwable $exception): void
    {
        $this->item->update([
            'status' => ImportItemStatus::Failed,
            'warnings' => ['Automatic analysis failed. Review this image manually or retry it later.'],
        ]);
        app(RefreshImportBatchProgress::class)->handle($this->item->batch);
        Log::error('Import item analysis failed', [
            'import_item_id' => $this->item->id,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }
}
