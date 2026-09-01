<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Services\Orders\OrderItemImageSnapshot;

class DeleteOrder
{
    public function __construct(private readonly OrderItemImageSnapshot $images) {}

    public function handle(Order $order): void
    {
        $order->delete();
        $this->images->deleteOrderSnapshots($order);
    }
}
