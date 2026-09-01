<?php

namespace App\Services\Cart;

use App\DTOs\CartLineData;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Services\Products\ProductPresenter;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const SESSION_KEY = 'tabarak.cart';

    public function __construct(private readonly ProductPresenter $products) {}

    public function add(Product $product, int $quantity): void
    {
        $this->assertOrderable($product);
        $cart = $this->raw();
        $cart[$product->id] = min(999, ($cart[$product->id] ?? 0) + $quantity);
        session()->put(self::SESSION_KEY, $cart);
    }

    public function update(Product $product, int $quantity): void
    {
        $this->assertOrderable($product);
        $cart = $this->raw();
        $cart[$product->id] = $quantity;
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

        foreach ($cart as $productId => $quantity) {
            $product = $products->get($productId);
            if (! $product || Money::toCents($product->unit_price) < 1) {
                continue;
            }

            $validCart[$product->id] = $quantity;
            $unitPriceCents = Money::toCents($product->unit_price);
            $lines->push(new CartLineData(
                product: $product,
                quantity: $quantity,
                unitPriceCents: $unitPriceCents,
                lineTotalCents: $unitPriceCents * $quantity,
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
                'line_total' => Money::fromCents($line->lineTotalCents),
            ])->values(),
            'item_count' => $lines->sum(fn (CartLineData $line) => $line->quantity),
            'subtotal' => Money::fromCents($subtotalCents),
            'currency' => Money::CURRENCY,
        ];
    }

    /** @return array<string, int> */
    private function raw(): array
    {
        return collect(session()->get(self::SESSION_KEY, []))
            ->mapWithKeys(fn ($quantity, $productId) => [(int) $productId => max(1, min(999, (int) $quantity))])
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
    }
}
