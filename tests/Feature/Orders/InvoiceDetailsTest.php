<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_order_keeps_optional_invoice_details(): void
    {
        $product = Product::factory()->create([
            'unit_price' => 20.75,
            'stock_quantity' => 20,
        ]);

        $response = $this->withSession(['tabarak.cart' => [$product->id => 2]])
            ->post(route('orders.store'), [
                'customer_name' => 'Sample Market LLC',
                'customer_phone' => '+1 (248) 555-1234',
                'purchase_order_number' => 'PO-1842',
                'billing_address' => "14319 Ford Rd\nDearborn, MI 48126",
                'shipping_address' => "14400 Michigan Ave\nDearborn, MI 48126",
            ]);

        $order = Order::query()->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('PO-1842', $order->purchase_order_number);
        $this->assertSame("14319 Ford Rd\nDearborn, MI 48126", $order->billing_address);
        $this->assertSame("14400 Michigan Ave\nDearborn, MI 48126", $order->shipping_address);
        $this->assertSame('41.50', $order->total);
        $this->assertSame(18, $product->fresh()->stock_quantity);
    }

    public function test_admin_can_set_delivery_and_payment_details_for_invoice(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->makeOrder();

        $this->actingAs($admin)
            ->patch(route('admin.orders.invoice-details.update', $order), [
                'purchase_order_number' => 'PO-9001',
                'billing_address' => "14319 Ford Rd\nDearborn, MI 48126",
                'shipping_address' => '',
                'delivery_date' => '2026-10-08',
                'payment_method' => 'check',
                'check_number' => '1184',
            ])
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('PO-9001', $order->purchase_order_number);
        $this->assertSame('2026-10-08', $order->delivery_date?->format('Y-m-d'));
        $this->assertSame('check', $order->payment_method?->value);
        $this->assertSame('1184', $order->check_number);
        $this->assertNull($order->shipping_address);
    }

    public function test_switching_payment_from_check_to_cash_clears_check_number(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->makeOrder([
            'payment_method' => 'check',
            'check_number' => '1009',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.invoice-details.update', $order), [
                'payment_method' => 'cash',
                'check_number' => '1009',
            ])
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame('cash', $order->payment_method?->value);
        $this->assertNull($order->check_number);
    }

    /** @param array<string, mixed> $overrides */
    private function makeOrder(array $overrides = []): Order
    {
        return Order::query()->create([
            'order_number' => 'TT-20261005-'.Str::upper(Str::random(6)),
            'public_token' => (string) Str::uuid(),
            'customer_name' => 'Sample Market LLC',
            'customer_phone' => '+12485551234',
            'status' => OrderStatus::Pending,
            'currency' => 'USD',
            'subtotal' => '20.75',
            'total' => '20.75',
            'submitted_at' => now(),
            ...$overrides,
        ]);
    }
}
