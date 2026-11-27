<?php

namespace Tests\Feature\Admin;

use App\Enums\ImportItemStatus;
use App\Jobs\AnalyzeImportItem;
use App\Models\Category;
use App\Models\ImportBatch;
use App\Models\ImportItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ImportItemReviewTest extends TestCase
{
    use RefreshDatabase;

    private ?string $testMediaRoot = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep test requests independent of a deployment URL or subdirectory.
        URL::forceRootUrl('http://localhost');
        URL::forceScheme('http');

        // Use a unique disposable disk outside the checkout. On Windows the
        // project's default fake disk may be unwritable or have mixed separators.
        $this->testMediaRoot = rtrim(sys_get_temp_dir(), '/\\\\')
            . DIRECTORY_SEPARATOR . 'tabarak-import-' . bin2hex(random_bytes(12));
        $catalogue = $this->testMediaRoot . DIRECTORY_SEPARATOR . 'catalogue'
            . DIRECTORY_SEPARATOR . now()->format('Y') . DIRECTORY_SEPARATOR . now()->format('m');
        if (! mkdir($catalogue, 0700, true) && ! is_dir($catalogue)) {
            throw new \RuntimeException('Cannot create isolated test media directory: '.$catalogue);
        }

        Storage::set('public', Storage::build([
            'driver' => 'local',
            'root' => $this->testMediaRoot,
            'throw' => true,
        ]));
    }

    protected function tearDown(): void
    {
        try {
            if ($this->testMediaRoot !== null) {
                File::deleteDirectory($this->testMediaRoot);
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_review_item_without_ai_name_can_be_published_with_manual_values(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.imports.store'), [
                'name' => 'Manual review batch',
                'images' => [
                    UploadedFile::fake()->image('unknown-product.jpg'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonStructure(['batch_id', 'upload_url', 'show_url']);

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
                    'unit_price' => '18.75',
                ],
            )
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Manual Cashews',
            'pack_quantity' => 12,
            'allows_open_quantity' => true,
            'unit_price' => 18.75,
        ]);

        $this->assertDatabaseHas('brands', [
            'name' => 'Manual Brand',
        ]);

        $this->assertDatabaseHas('import_items', [
            'id' => $item->id,
            'status' => ImportItemStatus::Approved->value,
        ]);
    }

    public function test_review_persists_corrected_ai_fields_and_validates_price(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->post(route('admin.imports.store'), [
            'name' => 'Priced review',
            'images' => [UploadedFile::fake()->image('peach.jpg')],
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonStructure(['batch_id', 'upload_url', 'show_url']);

        $item = ImportItem::query()->firstOrFail();
        $item->update([
            'status' => ImportItemStatus::Review,
            'suggested_name' => 'Peach Candy',
            'suggested_weight' => '75g',
            'suggested_metadata' => ['description' => 'Candy', 'detected_text' => 'Peach', 'sku' => 'OLD-SKU'],
        ]);

        $url = route('admin.imports.items.publish.store', [$item->import_batch_id, $item->id]);
        $data = [
            'name' => 'Peach Ice Cream',
            'category_id' => $category->id,
            'brand' => null,
            'pack_quantity' => 12,
            'allows_open_quantity' => true,
            'sku' => 'PEACH-NEW',
            'short_description' => 'Peach flavored ice cream',
            'description' => 'Corrected package text',
            'weight_value' => 80,
            'weight_unit' => 'g',
            'unit_price' => '12.50',
        ];

        foreach (['-1', '0', '12.345'] as $invalid) {
            $this->actingAs($admin)->post($url, array_replace($data, ['unit_price' => $invalid]))
                ->assertSessionHasErrors('unit_price');
        }

        $this->assertDatabaseCount('products', 0);

        $this->actingAs($admin)->post($url, $data)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Peach Ice Cream',
            'category_id' => $category->id,
            'sku' => 'PEACH-NEW',
            'short_description' => 'Peach flavored ice cream',
            'description' => 'Corrected package text',
            'weight_value' => 80,
            'weight_unit' => 'g',
            'unit_price' => 12.50,
            'pack_quantity' => 12,
        ]);

        $this->assertSame(ImportItemStatus::Approved, $item->fresh()->status);
    }

    public function test_priced_batch_publishes_individual_prices_and_leaves_unpriced_items(): void
    {
        Queue::fake();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.imports.store'), [
            'name' => '100 item batch',
            'images' => [UploadedFile::fake()->image('bulk.jpg')],
        ], ['Accept' => 'application/json'])->assertCreated();

        $batch = ImportBatch::query()->findOrFail($response->json('batch_id'));
        $first = $batch->items()->firstOrFail();
        $first->update(['status' => ImportItemStatus::Review, 'suggested_name' => 'Product 1']);
        $drafts = [];
        for ($i = 1; $i <= 100; $i++) {
            $item = $i === 1 ? $first : $batch->items()->create([
                'media_id' => $first->media_id,
                'status' => ImportItemStatus::Review,
                'suggested_name' => 'Product '.$i,
            ]);
            if ($i !== 100) {
                $drafts[] = [
                    'id' => $item->id,
                    'name' => 'Corrected product '.$i,
                    'sku' => null,
                    'unit_price' => number_format($i + 0.25, 2, '.', ''),
                    'allows_open_quantity' => $i === 2,
                ];
            }
        }

        $otherBatch = ImportBatch::query()->create(['created_by' => $admin->id, 'name' => 'Other batch']);
        $foreign = $otherBatch->items()->create([
            'media_id' => $first->media_id,
            'status' => ImportItemStatus::Review,
            'suggested_name' => 'Other product',
        ]);
        $url = route('admin.imports.publish.store', $batch);

        $bad = $drafts;
        $bad[1]['unit_price'] = '-1';
        $this->actingAs($admin)->postJson($url, ['items' => $bad])
            ->assertUnprocessable()->assertJsonValidationErrors('items.1.unit_price');
        $bad = $drafts;
        $bad[1]['id'] = $foreign->id;
        $this->actingAs($admin)->postJson($url, ['items' => $bad])
            ->assertUnprocessable()->assertJsonValidationErrors('items.1.id');
        $this->assertDatabaseCount('products', 0);

        $this->actingAs($admin)->postJson($url, ['items' => $drafts])->assertRedirect();
        $this->assertDatabaseCount('products', 99);
        $this->assertDatabaseHas('products', ['name' => 'Corrected product 1', 'unit_price' => 1.25]);
        $this->assertDatabaseHas('products', ['name' => 'Corrected product 2', 'unit_price' => 2.25, 'allows_open_quantity' => true]);
        $this->assertDatabaseHas('products', ['name' => 'Corrected product 99', 'unit_price' => 99.25]);
        $this->assertSame(1, $batch->items()->where('status', ImportItemStatus::Review)->count());
        $this->assertSame(99, $batch->fresh()->approved_items);
        $this->assertSame(ImportItemStatus::Review, $foreign->fresh()->status);
    }

    public function test_publish_without_prices_and_fill_them_later_in_bulk(): void
    {
        Queue::fake();
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->post(route('admin.imports.store'), [
            'name' => 'Missing prices',
            'images' => [UploadedFile::fake()->image('missing-price.jpg')],
        ], ['Accept' => 'application/json'])->assertCreated();
        $batch = ImportBatch::query()->findOrFail($response->json('batch_id'));
        $first = $batch->items()->firstOrFail();
        $first->update(['status' => ImportItemStatus::Review, 'suggested_name' => 'Product 1']);
        $drafts = [];
        for ($i = 1; $i <= 100; $i++) {
            $item = $i === 1 ? $first : $batch->items()->create([
                'media_id' => $first->media_id,
                'status' => ImportItemStatus::Review,
                'suggested_name' => 'Product '.$i,
            ]);
            $drafts[] = [
                'id' => $item->id,
                'name' => 'Product '.$i,
                'unit_price' => null,
                'allows_open_quantity' => false,
            ];
        }

        // The individual and bulk paths both permit missing prices.
        $single = array_shift($drafts);
        $this->actingAs($admin)->post(route('admin.imports.items.publish.store', [$batch->id, $single['id']]), $single)
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($admin)->postJson(route('admin.imports.publish.store', $batch), ['items' => $drafts])
            ->assertRedirect();
        $this->assertDatabaseCount('products', 100);
        $this->assertSame(100, \App\Models\Product::query()->whereNull('unit_price')->count());

        $url = route('admin.imports.prices.update', $batch);
        $items = $batch->items()->orderBy('id')->get();
        $prices = $items->map(fn ($item, $index) => [
            'id' => $item->id,
            'unit_price' => number_format($index + 1.25, 2, '.', ''),
        ])->all();
        $otherBatch = ImportBatch::query()->create(['created_by' => $admin->id, 'name' => 'Other batch']);
        $foreign = $otherBatch->items()->create([
            'media_id' => $first->media_id,
            'status' => ImportItemStatus::Review,
            'suggested_name' => 'Other product',
        ]);
        $bad = $prices;
        $bad[1]['id'] = $foreign->id;
        $this->actingAs($admin)->patchJson($url, ['items' => $bad])
            ->assertUnprocessable()->assertJsonValidationErrors('items.1.id');
        $bad = $prices;
        $bad[1]['unit_price'] = '0';
        $this->actingAs($admin)->patchJson($url, ['items' => $bad])
            ->assertUnprocessable()->assertJsonValidationErrors('items.1.unit_price');
        $this->assertSame(100, \App\Models\Product::query()->whereNull('unit_price')->count());

        $this->actingAs($admin)->patchJson($url, ['items' => $prices])->assertRedirect();
        $this->assertDatabaseCount('products', 100);
        $this->assertDatabaseHas('products', ['name' => 'Product 1', 'unit_price' => 1.25]);
        $this->assertDatabaseHas('products', ['name' => 'Product 100', 'unit_price' => 100.25]);
        $this->assertSame(0, \App\Models\Product::query()->whereNull('unit_price')->count());
        $this->actingAs($admin)->patchJson($url, ['items' => [['id' => $first->id, 'unit_price' => '9.99']]])
            ->assertUnprocessable()->assertJsonValidationErrors('items.0.id');
        $this->assertDatabaseHas('products', ['name' => 'Product 1', 'unit_price' => 1.25]);
    }

    public function test_unapproved_import_item_can_be_deleted_with_orphaned_media(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.imports.store'), [
                'name' => 'Delete review batch',
                'images' => [
                    UploadedFile::fake()->image('bad-import.jpg'),
                ],
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonStructure(['batch_id', 'upload_url', 'show_url']);

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
