<?php

namespace App\Actions\Orders;

use App\DTOs\CartLineData;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Orders\OrderItemImageSnapshot;
use App\Services\Orders\OrderNumberGenerator;
use App\Services\Products\ProductInventory;
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
        private readonly ProductInventory $inventory,
    ) {}

    public function handle(string $customerName, string $customerPhone, ?string $customerAddress = null): Order
    {
        $lines = $this->cart->lines();
        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $token = (string) Str::uuid();

        try {
            $order = DB::transaction(function () use ($customerName, $customerPhone, $customerAddress, $lines, $token): Order {
                $products = Product::query()
                    ->whereKey($lines->map(fn (CartLineData $line) => $line->product->id))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $preparedLines = $lines->map(function (CartLineData $line) use ($products): array {
                    /** @var Product|null $product */
                    $product = $products->get($line->product->id);

                    if (! $product
                        || $product->status !== ProductStatus::Published
                        || ! $product->published_at?->isPast()
                        || $product->unit_price === null
                        || Money::toCents($product->unit_price) < 1) {
                        throw ValidationException::withMessages(['cart' => $line->product->name.' is no longer available to order.']);
                    }

                    $this->inventory->assertAvailable($product, $line->quantity);
                    $unitPriceCents = $line->unitPriceCents;

                    return [
                        'product' => $product,
                        'quantity' => $line->quantity,
                        'unit_price_cents' => $unitPriceCents,
                        'line_total_cents' => $unitPriceCents * $line->quantity,
                    ];
                });
                $subtotalCents = $preparedLines->sum('line_total_cents');
                $order = Order::query()->create([
                    'order_number' => $this->numbers->generate(),
                    'public_token' => $token,
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'customer_address' => $customerAddress,
                    'status' => OrderStatus::Pending,
                    'currency' => Money::CURRENCY,
                    'subtotal' => Money::fromCents($subtotalCents),
                    'total' => Money::fromCents($subtotalCents),
                    'submitted_at' => now(),
                ]);

                $preparedLines->each(function (array $line) use ($order, $token): void {
                    /** @var Product $product */
                    $product = $line['product'];
                    $stockReserved = $this->inventory->reserve($product, $line['quantity']);
                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'pack_label' => ProductPackLabel::for($product),
                        ...$this->images->store($product, $token),
                        'unit_price' => Money::fromCents($line['unit_price_cents']),
                        'quantity' => $line['quantity'],
                        'stock_reserved' => $stockReserved,
                        'line_total' => Money::fromCents($line['line_total_cents']),
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
