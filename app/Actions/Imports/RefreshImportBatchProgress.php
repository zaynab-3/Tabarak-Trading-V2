<?php

namespace App\Actions\Imports;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportItemStatus;
use App\Models\ImportBatch;

class RefreshImportBatchProgress
{
    public function handle(ImportBatch $batch): ImportBatch
    {
        $total = $batch->items()->count();
        $processed = $batch->items()->whereIn('status', [
            ImportItemStatus::Review,
            ImportItemStatus::Approved,
            ImportItemStatus::Rejected,
            ImportItemStatus::Failed,
        ])->count();
        $failed = $batch->items()->where('status', ImportItemStatus::Failed)->count();

        $status = match (true) {
            $total === 0 => ImportBatchStatus::Pending,
            $processed < $total => ImportBatchStatus::Processing,
            $failed === $total => ImportBatchStatus::Failed,
            default => ImportBatchStatus::Review,
        };

        $batch->update([
            'total_items' => $total,
            'processed_items' => $processed,
            'status' => $status,
            'failure_reason' => $failed > 0 ? "$failed image(s) could not be analyzed and need manual review." : null,
        ]);

        return $batch->refresh();
    }
}
