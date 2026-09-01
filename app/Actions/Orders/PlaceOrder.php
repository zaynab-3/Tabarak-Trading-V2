<?php

namespace App\Actions\Orders;

use App\DTOs\CartLineData;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Cart\CartService;
use App\Services\Orders\OrderItemImageSnapshot;
use App\Services\Orders\OrderNumberGenerator;
use App\Support\Money;
use App\Support\ProductPackLabel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PlaceOrder
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderNumberGenerator $numbers,
        private readonly OrderItemImageSnapshot $images,
    ) {}

    public function handle(string $customerName, string $customerPhone): Order
    {
        $lines = $this->cart->lines();
        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $token = (string) Str::uuid();

        try {
            $order = DB::transaction(function () use ($customerName, $customerPhone, $lines, $token): Order {
                $subtotalCents = $lines->sum(fn (CartLineData $line) => $line->lineTotalCents);
                $order = Order::query()->create([
                    'order_number' => $this->numbers->generate(),
                    'public_token' => $token,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'status' => OrderStatus::Pending,
                    'currency' => Money::CURRENCY,
                    'subtotal' => Money::fromCents($subtotalCents),
                    'total' => Money::fromCents($subtotalCents),
                    'submitted_at' => now(),
                ]);

                $lines->each(function (CartLineData $line) use ($order, $token): void {
                    $order->items()->create([
                        'product_id' => $line->product->id,
                        'product_name' => $line->product->name,
                        'product_sku' => $line->product->sku,
                        'pack_label' => ProductPackLabel::for($line->product),
                        ...$this->images->store($line->product, $token),
                        'unit_price' => Money::fromCents($line->unitPriceCents),
                        'quantity' => $line->quantity,
                        'line_total' => Money::fromCents($line->lineTotalCents),
                    ]);
                });

                return $order;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->deleteDirectory('order-invoices/'.$token);
            throw $exception;
        }

        $this->cart->clear();

        return $order->load('items');
    }
}
