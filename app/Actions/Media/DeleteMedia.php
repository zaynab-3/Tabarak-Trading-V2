<?php

namespace App\Actions\Media;

use App\Actions\Imports\RefreshImportBatchProgress;
use App\Models\ImportBatch;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteMedia
{
    public function __construct(private readonly RefreshImportBatchProgress $refreshProgress) {}

    public function handle(Media $media): void
    {
        $disk = $media->disk;
        $path = $media->path;
        $productIds = $media->productImages()->pluck('product_id')->unique()->values();
        $batchIds = $media->importItems()->pluck('import_batch_id')->unique()->values();

        DB::transaction(function () use ($media, $productIds): void {
            $media->productImages()->delete();
            $media->importItems()->delete();
            $media->delete();

            Product::query()->whereKey($productIds)->each(function (Product $product): void {
                if (! $product->images()->where('is_primary', true)->exists()) {
                    $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
                }
            });
        });

        ImportBatch::query()->whereKey($batchIds)->each(
            fn (ImportBatch $batch) => $this->refreshProgress->handle($batch),
        );
        Storage::disk($disk)->delete($path);
    }
}
