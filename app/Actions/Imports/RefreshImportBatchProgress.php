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
        $approved = $batch->items()->where('status', ImportItemStatus::Approved)->count();
        $rejected = $batch->items()->where('status', ImportItemStatus::Rejected)->count();

        $status = match (true) {
            $total === 0 => ImportBatchStatus::Pending,
            $processed < $total => ImportBatchStatus::Processing,
            $failed === $total => ImportBatchStatus::Failed,
            ($approved + $rejected) === $total => ImportBatchStatus::Completed,
            default => ImportBatchStatus::Review,
        };

        $batch->update([
            'total_items' => $total,
            'processed_items' => $processed,
            'approved_items' => $approved,
            'rejected_items' => $rejected,
            'status' => $status,
            'failure_reason' => $failed > 0 ? "$failed image(s) could not be analyzed and need manual review." : null,
        ]);

        return $batch->refresh();
    }
}
