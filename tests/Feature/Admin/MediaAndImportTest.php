<?php

namespace Tests\Feature\Admin;

use App\Jobs\AnalyzeImportItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaAndImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_upload_rejects_non_images(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.media.store'), [
            'images' => [UploadedFile::fake()->create('catalogue.txt', 10, 'text/plain')],
        ])->assertSessionHasErrors('images.0');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_import_batch_creates_related_items_and_queues_analysis(): void
    {
        Storage::fake('public');
        Queue::fake();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.imports.store'), [
            'name' => 'Nuts September',
            'images' => [UploadedFile::fake()->image('almonds.jpg'), UploadedFile::fake()->image('cashews.png')],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('import_batches', ['name' => 'Nuts September', 'created_by' => $admin->id, 'total_items' => 2]);
        $this->assertDatabaseCount('import_items', 2);
        Queue::assertPushed(AnalyzeImportItem::class, 2);
    }
}
