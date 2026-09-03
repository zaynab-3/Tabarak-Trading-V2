<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCustomPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_set_custom_unit_price(): void
    {
        $product = Product::factory()->create([
            'unit_price' => '25.00',
            'stock_quantity' => 50,
        ]);

        $response = $this->post(route('cart.items.store', $product->slug), [
            'quantity' => 2,
            'custom_unit_price' => '10.00',
        ]);

        $response->assertSessionHas('success');

        $cart = app(CartService::class)->summary();
        $this->assertSame(1, $cart['item_count'] ? 1 : 0);
        $this->assertFalse($cart['has_custom_prices']);

        $item = $cart['items'][0];
        $this->assertSame('25.00', $item['unit_price']);
        $this->assertSame('50.00', $item['line_total']);
        $this->assertFalse($item['is_custom_price']);
    }

    public function test_admin_can_add_product_with_custom_unit_price(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'unit_price' => '25.00',
            'stock_quantity' => 50,
        ]);

        $response = $this->actingAs($admin)->post(route('cart.items.store', $product->slug), [
            'quantity' => 3,
            'custom_unit_price' => '18.50',
        ]);

        $response->assertSessionHas('success');

        $cart = app(CartService::class)->summary();
        $this->assertTrue($cart['has_custom_prices']);

        $item = $cart['items'][0];
        $this->assertSame('18.50', $item['unit_price']);
        $this->assertSame('25.00', $item['original_unit_price']);
        $this->assertTrue($item['is_custom_price']);
        $this->assertSame('55.50', $item['line_total']);
        $this->assertSame('55.50', $cart['subtotal']);
    }

    public function test_admin_can_update_custom_unit_price_in_cart(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'unit_price' => '30.00',
            'stock_quantity' => 50,
        ]);

        $this->actingAs($admin)->post(route('cart.items.store', $product->slug), [
            'quantity' => 2,
        ]);

        $response = $this->actingAs($admin)->patch(route('cart.items.update', $product->slug), [
            'quantity' => 2,
            'custom_unit_price' => '22.00',
        ]);

        $response->assertSessionHas('success');

        $cart = app(CartService::class)->summary();
        $item = $cart['items'][0];
        $this->assertSame('22.00', $item['unit_price']);
        $this->assertSame('30.00', $item['original_unit_price']);
        $this->assertTrue($item['is_custom_price']);
        $this->assertSame('44.00', $item['line_total']);
    }

    public function test_admin_can_reset_custom_price_back_to_catalog(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'unit_price' => '40.00',
            'stock_quantity' => 50,
        ]);

        $this->actingAs($admin)->post(route('cart.items.store', $product->slug), [
            'quantity' => 1,
            'custom_unit_price' => '32.00',
        ]);

        $response = $this->actingAs($admin)->patch(route('cart.items.update', $product->slug), [
            'quantity' => 1,
            'reset_custom_price' => true,
        ]);

        $response->assertSessionHas('success');

        $cart = app(CartService::class)->summary();
        $item = $cart['items'][0];
        $this->assertSame('40.00', $item['unit_price']);
        $this->assertFalse($item['is_custom_price']);
        $this->assertFalse($cart['has_custom_prices']);
    }

    public function test_placing_order_persists_custom_admin_unit_price(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'unit_price' => '50.00',
            'stock_quantity' => 50,
        ]);

        $this->actingAs($admin)->post(route('cart.items.store', $product->slug), [
            'quantity' => 2,
            'custom_unit_price' => '35.00',
        ]);

        $response = $this->actingAs($admin)->post(route('orders.store'), [
            'customer_name' => 'Wholesale Market Inc',
            'customer_phone' => '+1 (202) 555 0199',
            'customer_address' => '123 Market St, Suite 400, Chicago, IL 60601',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Wholesale Market Inc',
            'customer_phone' => '+12025550199',
            'customer_address' => '123 Market St, Suite 400, Chicago, IL 60601',
            'subtotal' => '70.00',
            'total' => '70.00',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'unit_price' => '35.00',
            'quantity' => 2,
            'line_total' => '70.00',
        ]);
    }

    public function test_order_placement_requires_customer_address(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'unit_price' => '20.00',
            'stock_quantity' => 10,
        ]);

        $this->actingAs($admin)->post(route('cart.items.store', $product->slug), [
            'quantity' => 1,
        ]);

        $response = $this->actingAs($admin)->post(route('orders.store'), [
            'customer_name' => 'Wholesale Market Inc',
            'customer_phone' => '+1 (202) 555 0199',
            'customer_address' => '',
        ]);

        $response->assertSessionHasErrors(['customer_address']);
        $this->assertDatabaseCount('orders', 0);
    }
}
