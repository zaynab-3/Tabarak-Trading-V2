<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Imports\PublishImportBatch;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\PublishImportBatchRequest;
use App\Models\ImportBatch;
use App\Models\Product;
use App\Enums\ImportItemStatus;
use App\Enums\ProductStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;

class ImportBatchPublicationController extends Controller
{
    public function store(
        PublishImportBatchRequest $request,
        ImportBatch $importBatch,
        PublishImportBatch $action,
    ): RedirectResponse {
        Gate::authorize('update', $importBatch);
        $count = $action->handle($importBatch, $request->validated());

        return back()->with('success', "$count product(s) published to the shop with their images.");
    }

    public function prices(Request $request, ImportBatch $importBatch): RedirectResponse
    {
        Gate::authorize('update', $importBatch);
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:250'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
        ]);

        $count = DB::transaction(function () use ($importBatch, $data): int {
            $items = $importBatch->items()->whereIn('id', array_column($data['items'], 'id'))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $products = Product::query()->whereIn('id', $items->pluck('approved_product_id')->filter())
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $updates = [];
            foreach ($data['items'] as $index => $draft) {
                $item = $items->get($draft['id']);
                $product = $item ? $products->get($item->approved_product_id) : null;
                if (! $item || $item->status !== ImportItemStatus::Approved || ! $product
                    || $product->status !== ProductStatus::Published || $product->unit_price !== null) {
                    throw ValidationException::withMessages([
                        "items.{$index}.id" => 'This product is no longer published without a price. Refresh and try again.',
                    ]);
                }
                Gate::authorize('update', $product);
                if (isset($updates[$product->id])) {
                    throw ValidationException::withMessages(["items.{$index}.id" => 'Duplicate product.']);
                }
                $updates[$product->id] = [$product, $draft['unit_price']];
            }
            foreach ($updates as [$product, $price]) {
                $product->update(['unit_price' => $price]);
            }

            return count($updates);
        });

        return back()->with('success', "$count product price(s) saved.");
    }
}
