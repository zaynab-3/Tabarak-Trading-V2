<?php

namespace App\Actions\Imports;

use App\Enums\ImportItemStatus;
use App\Models\ImportBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishImportBatch
{
    public function __construct(private readonly ApproveImportItem $approveItem) {}

    /** @param array<string, mixed> $overrides */
    public function handle(ImportBatch $batch, array $overrides = []): int
    {
        if (array_key_exists('items', $overrides)) {
            return $this->publishPricedItems($batch, $overrides['items']);
        }

        $items = $batch->items()
            ->where('status', ImportItemStatus::Review)
            ->whereNull('approved_product_id')
            ->whereNotNull('suggested_name')
            ->get();

        $items->each(fn ($item) => $this->approveItem->handle($item, $overrides));

        return $items->count();
    }

    /** @param array<int, array<string, mixed>> $drafts */
    private function publishPricedItems(ImportBatch $batch, array $drafts): int
    {
        return DB::transaction(function () use ($batch, $drafts): int {
            $items = $batch->items()
                ->whereIn('id', array_column($drafts, 'id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($drafts as $index => $draft) {
                $item = $items->get($draft['id']);
                if (! $item || $item->approved_product_id !== null
                    || ! in_array($item->status, [ImportItemStatus::Review, ImportItemStatus::Failed], true)) {
                    throw ValidationException::withMessages([
                        "items.{$index}.id" => 'This item is no longer available for publication in this batch.',
                    ]);
                }
            }

            foreach ($drafts as $draft) {
                $item = $items->get($draft['id']);
                unset($draft['id']);
                $this->approveItem->handle($item, $draft);
            }

            return count($drafts);
        });
    }
}
