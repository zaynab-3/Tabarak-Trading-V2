<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = 'TT-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
