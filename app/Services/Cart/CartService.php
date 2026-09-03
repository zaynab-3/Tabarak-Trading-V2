<?php

namespace App\Services\Cart;

use App\DTOs\CartLineData;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\Products\ProductInventory;
use App\Services\Products\ProductPresenter;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const SESSION_KEY = 'tabarak.cart';

    public function __construct(
        private readonly ProductPresenter $products,
        private readonly ProductInventory $inventory,
    ) {}

    public function add(Product $product, int $quantity, ?int $customUnitPriceCents = null): void
    {
        $this->assertOrderable($product);
        $cart = $this->raw();
        $existing = $cart[$product->id] ?? null;
        $existingQuantity = $existing['quantity'] ?? 0;
        $newQuantity = min(999, $existingQuantity + $quantity);
        $this->inventory->assertAvailable($product, $newQuantity);

        $customPrice = $customUnitPriceCents !== null && $customUnitPriceCents > 0
            ? $customUnitPriceCents
            : ($existing['custom_unit_price_cents'] ?? null);

        $cart[$product->id] = [
            'quantity' => $newQuantity,
            'custom_unit_price_cents' => $customPrice,
        ];

        session()->put(self::SESSION_KEY, $cart);
    }

    public function update(Product $product, int $quantity, ?int $customUnitPriceCents = null, bool $resetCustomPrice = false): void
    {
        $this->assertOrderable($product);
        $this->inventory->assertAvailable($product, $quantity);
        $cart = $this->raw();
        $existing = $cart[$product->id] ?? null;

        if ($resetCustomPrice) {
            $customPrice = null;
        } elseif ($customUnitPriceCents !== null && $customUnitPriceCents > 0) {
            $customPrice = $customUnitPriceCents;
        } else {
            $customPrice = $existing['custom_unit_price_cents'] ?? null;
        }

        $cart[$product->id] = [
            'quantity' => $quantity,
            'custom_unit_price_cents' => $customPrice,
        ];

        session()->put(self::SESSION_KEY, $cart);
    }

    public function remove(Product $product): void
    {
        $cart = $this->raw();
        unset($cart[$product->id]);
        session()->put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /** @return Collection<int, CartLineData> */
    public function lines(): Collection
    {
        $cart = $this->raw();
        if ($cart === []) {
            return collect();
        }

        $products = Product::query()
            ->published()
            ->whereNotNull('unit_price')
            ->whereIn('id', array_keys($cart))
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'primaryImage.media'])
            ->get()
            ->keyBy('id');

        $validCart = [];
        $lines = collect();
        $user = Auth::user();
        $isAdmin = $user instanceof User && $user->isAdmin();

        foreach ($cart as $productId => $item) {
            $product = $products->get($productId);
            if (! $product || Money::toCents($product->unit_price) < 1) {
                continue;
            }

            $maximum = $this->inventory->maximumOrderQuantity($product);
            if ($maximum < 1) {
                continue;
            }

            $quantity = min($item['quantity'], $maximum);
            $catalogUnitPriceCents = Money::toCents($product->unit_price);

            $hasCustomPrice = $isAdmin && ! empty($item['custom_unit_price_cents']) && $item['custom_unit_price_cents'] > 0;
            $effectiveUnitPriceCents = $hasCustomPrice ? (int) $item['custom_unit_price_cents'] : $catalogUnitPriceCents;

            $validCart[$product->id] = [
                'quantity' => $quantity,
                'custom_unit_price_cents' => $hasCustomPrice ? (int) $item['custom_unit_price_cents'] : null,
            ];

            $lines->push(new CartLineData(
                product: $product,
                quantity: $quantity,
                unitPriceCents: $effectiveUnitPriceCents,
                lineTotalCents: $effectiveUnitPriceCents * $quantity,
                originalUnitPriceCents: $catalogUnitPriceCents,
                isCustomPrice: $hasCustomPrice,
            ));
        }

        if ($validCart !== $cart) {
            session()->put(self::SESSION_KEY, $validCart);
        }

        return $lines;
    }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $lines = $this->lines();
        $subtotalCents = $lines->sum(fn (CartLineData $line) => $line->lineTotalCents);

        return [
            'items' => $lines->map(fn (CartLineData $line) => [
                'product' => $this->products->summary($line->product),
                'quantity' => $line->quantity,
                'unit_price' => Money::fromCents($line->unitPriceCents),
                'original_unit_price' => $line->originalUnitPriceCents !== null ? Money::fromCents($line->originalUnitPriceCents) : null,
                'is_custom_price' => $line->isCustomPrice,
                'line_total' => Money::fromCents($line->lineTotalCents),
            ])->values(),
            'item_count' => $lines->sum(fn (CartLineData $line) => $line->quantity),
            'subtotal' => Money::fromCents($subtotalCents),
            'has_custom_prices' => $lines->contains(fn (CartLineData $line) => $line->isCustomPrice),
            'currency' => Money::CURRENCY,
        ];
    }

    /** @return array<int, array{quantity: int, custom_unit_price_cents: ?int}> */
    private function raw(): array
    {
        return collect(session()->get(self::SESSION_KEY, []))
            ->mapWithKeys(function ($item, $productId) {
                $id = (int) $productId;
                if (is_array($item)) {
                    $quantity = max(1, min(999, (int) ($item['quantity'] ?? 1)));
                    $customPrice = isset($item['custom_unit_price_cents']) && is_numeric($item['custom_unit_price_cents']) && (int) $item['custom_unit_price_cents'] > 0
                        ? (int) $item['custom_unit_price_cents']
                        : null;

                    return [$id => ['quantity' => $quantity, 'custom_unit_price_cents' => $customPrice]];
                }

                return [$id => ['quantity' => max(1, min(999, (int) $item)), 'custom_unit_price_cents' => null]];
            })
            ->all();
    }

    private function assertOrderable(Product $product): void
    {
        $isPublished = $product->status === ProductStatus::Published
            && $product->published_at?->isPast();
        $hasPrice = $product->unit_price !== null && Money::toCents($product->unit_price) > 0;

        if (! $isPublished || ! $hasPrice) {
            throw ValidationException::withMessages([
                'cart' => 'This product is not currently available to order.',
            ]);
        }

        $this->inventory->assertAvailable($product, 1);
    }
}
