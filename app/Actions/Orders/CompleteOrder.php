<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;

class CompleteOrder
{
    public function handle(Order $order): void
    {
        $order->update([
            'status' => OrderStatus::Completed,
            'completed_at' => $order->completed_at ?? now(),
        ]);
    }
}
