<?php

namespace App\Actions\Imports;

use App\Actions\Media\StoreMedia;
use App\Jobs\AnalyzeImportItem;
use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateImportBatch
{
    public function __construct(private readonly StoreMedia $storeMedia) {}

    /** @param array<int, UploadedFile> $images */
    public function handle(User $creator, ?string $name, array $images): ImportBatch
    {
        $batch = DB::transaction(function () use ($creator, $name, $images) {
            $batch = ImportBatch::query()->create([
                'created_by' => $creator->id,
                'name' => $name ?: 'Import '.now()->format('M j, Y H:i'),
                'total_items' => count($images),
            ]);

            foreach ($images as $image) {
                $media = $this->storeMedia->handle($image);
                $batch->items()->create(['media_id' => $media->id]);
            }

            return $batch;
        });

        $batch->items()->each(fn ($item) => AnalyzeImportItem::dispatch($item)->afterCommit());

        return $batch;
    }
}
