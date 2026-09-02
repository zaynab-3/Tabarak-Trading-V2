<?php

namespace Tests\Feature\Admin;

use App\Enums\ImportItemStatus;
use App\Jobs\AnalyzeImportItem;
use App\Models\ImportItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportItemReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_item_without_ai_name_can_be_published_with_manual_values(): void
    {
        Storage::fake('public');
        Queue::fake();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.imports.store'), [
                'name' => 'Manual review batch',
                'images' => [
                    UploadedFile::fake()->image('unknown-product.jpg'),
                ],
            ])
            ->assertSessionHasNoErrors();

        Queue::assertPushed(AnalyzeImportItem::class);

        $item = ImportItem::query()->firstOrFail();

        $item->update([
            'status' => ImportItemStatus::Review,
            'suggested_name' => null,
            'suggested_brand' => null,
            'suggested_category' => null,
            'confidence' => 0,
        ]);

        $this->actingAs($admin)
            ->post(
                route(
                    'admin.imports.items.publish.store',
                    [$item->import_batch_id, $item->id],
                ),
                [
                    'name' => 'Manual Cashews',
                    'brand' => 'Manual Brand',
                    'category_id' => null,
                    'pack_quantity' => 12,
                    'allows_open_quantity' => true,
                ],
            )
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Manual Cashews',
            'pack_quantity' => 12,
            'allows_open_quantity' => true,
        ]);

        $this->assertDatabaseHas('brands', [
            'name' => 'Manual Brand',
        ]);

        $this->assertDatabaseHas('import_items', [
            'id' => $item->id,
            'status' => ImportItemStatus::Approved->value,
        ]);
    }

    public function test_unapproved_import_item_can_be_deleted_with_orphaned_media(): void
    {
        Storage::fake('public');
        Queue::fake();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.imports.store'), [
                'name' => 'Delete review batch',
                'images' => [
                    UploadedFile::fake()->image('bad-import.jpg'),
                ],
            ])
            ->assertSessionHasNoErrors();

        $item = ImportItem::query()
            ->with('media')
            ->firstOrFail();

        $item->update([
            'status' => ImportItemStatus::Review,
        ]);

        $batchId = $item->import_batch_id;
        $mediaId = $item->media_id;
        $path = $item->media->path;

        $this->assertTrue(
            Storage::disk('public')->exists($path),
        );

        $this->actingAs($admin)
            ->delete(
                route(
                    'admin.imports.items.destroy',
                    [$batchId, $item->id],
                ),
            )
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('import_items', [
            'id' => $item->id,
        ]);

        $this->assertDatabaseMissing('media', [
            'id' => $mediaId,
        ]);

        $this->assertDatabaseHas('import_batches', [
            'id' => $batchId,
            'total_items' => 0,
            'processed_items' => 0,
        ]);

        $this->assertFalse(
            Storage::disk('public')->exists($path),
        );
    }
}
