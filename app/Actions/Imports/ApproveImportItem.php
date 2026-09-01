<?php

namespace App\Actions\Imports;

use App\Actions\Media\AttachMediaToProduct;
use App\Actions\Products\CreateProduct;
use App\Enums\ImportItemStatus;
use App\Models\ImportItem;
use App\Models\Product;
use App\Services\Imports\ImportProductDataFactory;
use Illuminate\Support\Facades\DB;
use LogicException;

class ApproveImportItem
{
    public function __construct(
        private readonly ImportProductDataFactory $productData,
        private readonly CreateProduct $createProduct,
        private readonly AttachMediaToProduct $attachMedia,
        private readonly RefreshImportBatchProgress $refreshProgress,
    ) {}

    /** @param array<string, mixed> $overrides */
    public function handle(ImportItem $item, array $overrides = []): Product
    {
        if ($item->approvedProduct) {
            return $item->approvedProduct;
        }

        if ($item->status !== ImportItemStatus::Review || blank($overrides['name'] ?? $item->suggested_name)) {
            throw new LogicException('Only analyzed items with a product name can be published.');
        }

        $product = DB::transaction(function () use ($item, $overrides): Product {
            $product = $this->createProduct->handle($this->productData->make($item, $overrides));
            $this->attachMedia->handle($product, $item->media);
            $item->update([
                'status' => ImportItemStatus::Approved,
                'approved_product_id' => $product->id,
            ]);

            return $product;
        });

        $this->refreshProgress->handle($item->batch);

        return $product;
    }
}
