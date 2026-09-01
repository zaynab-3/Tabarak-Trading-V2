<?php

namespace App\Actions\Imports;

use App\Enums\ImportItemStatus;
use App\Models\ImportBatch;

class PublishImportBatch
{
    public function __construct(private readonly ApproveImportItem $approveItem) {}

    /** @param array<string, mixed> $overrides */
    public function handle(ImportBatch $batch, array $overrides = []): int
    {
        $items = $batch->items()
            ->where('status', ImportItemStatus::Review)
            ->whereNull('approved_product_id')
            ->whereNotNull('suggested_name')
            ->get();

        $items->each(fn ($item) => $this->approveItem->handle($item, $overrides));

        return $items->count();
    }
}
