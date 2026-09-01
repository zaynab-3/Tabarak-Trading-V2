<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreCartItemRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(CartService $cart): Response
    {
        return Inertia::render('Storefront/Cart', ['cart' => $cart->summary()]);
    }

    public function store(StoreCartItemRequest $request, Product $product, CartService $cart): RedirectResponse
    {
        $cart->add($product, (int) $request->validated('quantity'));

        return back()->with('success', $product->name.' added to your cart.');
    }

    public function update(UpdateCartItemRequest $request, Product $product, CartService $cart): RedirectResponse
    {
        $cart->update($product, (int) $request->validated('quantity'));

        return back()->with('success', 'Cart quantity updated.');
    }

    public function destroy(Product $product, CartService $cart): RedirectResponse
    {
        $cart->remove($product);

        return back()->with('success', $product->name.' removed from your cart.');
    }
}
