<?php

namespace App\Actions\Imports;

use App\Enums\ImportItemStatus;
use App\Jobs\AnalyzeImportItem;
use App\Models\ImportBatch;
use App\Models\ImportItem;
use Illuminate\Support\Facades\DB;

class ReanalyzeImportBatch
{
    public function __construct(private readonly RefreshImportBatchProgress $refreshProgress) {}

    public function handle(ImportBatch $batch): int
    {
        $items = $batch->items()
            ->whereIn('status', [ImportItemStatus::Review, ImportItemStatus::Failed])
            ->whereNull('approved_product_id')
            ->get();

        DB::transaction(function () use ($items): void {
            $items->each(function (ImportItem $item): void {
                $item->update([
                    'status' => ImportItemStatus::Pending,
                    'suggested_name' => null,
                    'suggested_brand' => null,
                    'suggested_category' => null,
                    'suggested_weight' => null,
                    'suggested_metadata' => null,
                    'confidence' => null,
                    'warnings' => null,
                    'provider_metadata' => null,
                ]);
            });
        });

        $this->refreshProgress->handle($batch);
        $items->each(fn (ImportItem $item) => AnalyzeImportItem::dispatch($item));

        return $items->count();
    }
}
