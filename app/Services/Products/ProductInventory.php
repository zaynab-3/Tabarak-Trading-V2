<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class ProductInventory
{
    public function tracks(Product $product): bool
    {
        return ! $product->allows_open_quantity && $product->stock_quantity !== null;
    }

    public function maximumOrderQuantity(Product $product): int
    {
        return $this->tracks($product) ? max(0, (int) $product->stock_quantity) : 999;
    }

    public function assertAvailable(Product $product, int $quantity): void
    {
        if ($quantity > $this->maximumOrderQuantity($product)) {
            throw ValidationException::withMessages([
                'cart' => $product->stock_quantity === 0
                    ? $product->name.' is currently out of stock.'
                    : 'Only '.$product->stock_quantity.' of '.$product->name.' remain in stock.',
            ]);
        }
    }

    public function reserve(Product $product, int $quantity): int
    {
        if (! $this->tracks($product)) {
            return 0;
        }

        $this->assertAvailable($product, $quantity);
        $product->decrement('stock_quantity', $quantity);

        return $quantity;
    }

    public function restore(Product $product, int $quantity): int
    {
        if ($quantity < 1 || ! $this->tracks($product)) {
            return 0;
        }

        $product->increment('stock_quantity', $quantity);

        return $quantity;
    }
}
