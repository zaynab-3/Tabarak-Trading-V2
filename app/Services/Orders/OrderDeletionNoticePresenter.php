<?php

namespace App\Services\Orders;

use App\Models\OrderDeletionNotice;

class OrderDeletionNoticePresenter
{
    /** @return array<string, mixed> */
    public function summary(OrderDeletionNotice $notice): array
    {
        return [
            'id' => $notice->id,
            'order_number' => $notice->order_number,
            'customer_name' => $notice->customer_name,
            'customer_phone' => $notice->customer_phone,
            'order_status' => $notice->order_status,
            'currency' => $notice->currency,
            'subtotal' => $notice->subtotal,
            'total' => $notice->total,
            'deletion_mode' => $notice->deletion_mode->value,
            'restored_quantity' => $notice->restored_quantity,
            'items_count' => collect($notice->items)->sum('quantity'),
            'submitted_at' => $notice->submitted_at?->toISOString(),
            'completed_at' => $notice->completed_at?->toISOString(),
            'recorded_at' => $notice->recorded_at?->toISOString(),
            'deleted_by' => $notice->deletedBy?->name,
        ];
    }

    /** @return array<string, mixed> */
    public function detail(OrderDeletionNotice $notice): array
    {
        return [
            ...$this->summary($notice),
            'items' => collect($notice->items)->map(fn (array $item) => [
                ...$item,
                'image_url' => filled($item['image_path'] ?? null)
                    ? route('admin.order-notices.items.image', [$notice, $item['id']])
                    : null,
            ])->values()->all(),
        ];
    }
}
