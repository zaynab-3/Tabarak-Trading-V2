<?php

namespace App\Actions\Imports;

use App\Enums\ImportItemStatus;
use App\Models\ImportItem;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LogicException;

class DeleteImportItem
{
    public function __construct(private readonly RefreshImportBatchProgress $refreshProgress) {}

    public function handle(ImportItem $item): void
    {
        if ($item->approved_product_id !== null
            || ! in_array($item->status, [ImportItemStatus::Review, ImportItemStatus::Failed], true)) {
            throw new LogicException('Only unpublished review items can be deleted.');
        }

        $batch = $item->batch()->firstOrFail();

        /** @var array{disk: string, path: string}|null $orphanedFile */
        $orphanedFile = DB::transaction(function () use ($item): ?array {
            $media = $item->media()->lockForUpdate()->first();

            $item->delete();

            if (! $media || $this->isReferenced($media)) {
                return null;
            }

            $file = [
                'disk' => $media->disk,
                'path' => $media->path,
            ];

            $media->delete();

            return $file;
        });

        $this->refreshProgress->handle($batch);

        if ($orphanedFile) {
            Storage::disk($orphanedFile['disk'])->delete($orphanedFile['path']);
        }
    }

    private function isReferenced(Media $media): bool
    {
        return $media->importItems()->exists()
            || $media->productImages()->exists()
            || $media->categoryImages()->exists()
            || $media->brandLogos()->exists();
    }
}
