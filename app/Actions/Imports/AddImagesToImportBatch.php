<?php

namespace App\Actions\Imports;

use App\Actions\Media\StoreMedia;
use App\Enums\ImportBatchStatus;
use App\Jobs\AnalyzeImportItem;
use App\Models\ImportBatch;
use App\Models\ImportItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AddImagesToImportBatch
{
    public function __construct(private readonly StoreMedia $storeMedia) {}

    /** @param array<int, UploadedFile> $images */
    public function handle(ImportBatch $batch, array $images): ImportBatch
    {
        /** @var Collection<int, ImportItem> $items */
        $items = DB::transaction(function () use ($batch, $images): Collection {
            $items = collect();

            foreach ($images as $image) {
                $media = $this->storeMedia->handle($image);
                $items->push($batch->items()->create(['media_id' => $media->id]));
            }

            $batch->increment('total_items', $items->count());
            $batch->update([
                'status' => ImportBatchStatus::Processing,
                'failure_reason' => null,
            ]);

            return $items;
        });

        $items->each(fn (ImportItem $item) => AnalyzeImportItem::dispatch($item));

        return $batch->refresh();
    }
}
