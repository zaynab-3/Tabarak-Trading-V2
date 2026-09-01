<?php

namespace App\Jobs;

use App\Enums\ImportBatchStatus;
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

    public function handle(ProductImageAnalyzerInterface $analyzer): void
    {
        $this->item->update(['status' => ImportItemStatus::Processing]);
        $this->item->batch()->update(['status' => ImportBatchStatus::Processing]);

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

        $batch = $this->item->batch;
        $batch->update([
            'processed_items' => $batch->items()->whereIn('status', [ImportItemStatus::Review, ImportItemStatus::Approved, ImportItemStatus::Rejected])->count(),
            'status' => ImportBatchStatus::Review,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $this->item->update(['status' => ImportItemStatus::Failed]);
        Log::error('Import item analysis failed', ['import_item_id' => $this->item->id, 'exception' => $exception::class]);
    }
}
