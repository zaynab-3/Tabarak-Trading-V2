<?php

namespace App\Actions\Orders;

use App\Enums\OrderDeletionMode;
use App\Models\Order;
use App\Models\OrderDeletionNotice;
use App\Models\Product;
use App\Models\User;
use App\Services\Products\ProductInventory;
use Illuminate\Support\Facades\DB;

class DeleteOrder
{
    public function __construct(
        private readonly ProductInventory $inventory,
    ) {}

    public function handle(Order $order, User $actor, OrderDeletionMode $mode): OrderDeletionNotice
    {
        $notice = DB::transaction(function () use ($order, $actor, $mode): OrderDeletionNotice {
            $order = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);
            $restoredQuantity = 0;
            $items = $order->items->map(function ($item) use ($mode, &$restoredQuantity): array {
                $restored = 0;

                if ($mode === OrderDeletionMode::CancelAndRestoreStock
                    && $item->stock_reserved > 0
                    && $item->product_id) {
                    $product = Product::query()->lockForUpdate()->find($item->product_id);
                    if ($product) {
                        $restored = $this->inventory->restore($product, $item->stock_reserved);
                        $restoredQuantity += $restored;
                    }
                }

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'pack_label' => $item->pack_label,
                    'image_disk' => $item->image_disk,
                    'image_path' => $item->image_path,
                    'image_mime_type' => $item->image_mime_type,
                    'image_alt_text' => $item->image_alt_text,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'stock_reserved' => $item->stock_reserved,
                    'stock_restored' => $restored,
                    'line_total' => $item->line_total,
                ];
            })->values()->all();

            $notice = OrderDeletionNotice::query()->create([
                'order_number' => $order->order_number,
                'public_token' => $order->public_token,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'order_status' => $order->status->value,
                'currency' => $order->currency,
                'subtotal' => $order->subtotal,
                'total' => $order->total,
                'deletion_mode' => $mode,
                'restored_quantity' => $restoredQuantity,
                'items' => $items,
                'submitted_at' => $order->submitted_at,
                'completed_at' => $order->completed_at,
                'deleted_by' => $actor->id,
                'recorded_at' => now(),
            ]);

            $order->delete();

            return $notice;
        });

        return $notice;
    }
}
