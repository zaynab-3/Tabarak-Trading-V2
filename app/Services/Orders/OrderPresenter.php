<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\OrderItem;

class OrderPresenter
{
    /** @return array<string, mixed> */
    public function summary(Order $order): array
    {
        return [
            'id' => $order->id,
            'public_token' => $order->public_token,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'status' => $order->status->value,
            'currency' => $order->currency,
            'subtotal' => $order->subtotal,
            'total' => $order->total,
            'items_count' => (int) ($order->items_count ?? $order->items->sum('quantity')),
            'submitted_at' => $order->submitted_at?->toISOString(),
            'completed_at' => $order->completed_at?->toISOString(),
        ];
    }

    /** @return array<string, mixed> */
    public function detail(Order $order): array
    {
        return [
            ...$this->summary($order),
            'items' => $order->items->map(fn (OrderItem $item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_sku' => $item->product_sku,
                'pack_label' => $item->pack_label,
                'image_url' => $item->image_path
                    ? route('orders.items.image', [$order, $item])
                    : null,
                'image_alt_text' => $item->image_alt_text,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
            ])->values(),
        ];
    }
}
