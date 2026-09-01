<?php

namespace App\Actions\Imports;

use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class CreateImportBatch
{
    public function __construct(private readonly AddImagesToImportBatch $addImages) {}

    /** @param array<int, UploadedFile> $images */
    public function handle(User $creator, ?string $name, array $images = []): ImportBatch
    {
        $batch = ImportBatch::query()->create([
            'created_by' => $creator->id,
            'name' => $name ?: 'Import '.now()->format('M j, Y H:i'),
        ]);

        return $images === [] ? $batch : $this->addImages->handle($batch, $images);
    }
}
