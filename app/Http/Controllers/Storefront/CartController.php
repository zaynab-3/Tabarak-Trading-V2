<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreCartItemRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(CartService $cart): Response
    {
        return Inertia::render('Storefront/Cart', ['cart' => $cart->summary()]);
    }

    public function store(StoreCartItemRequest $request, Product $product, CartService $cart): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user instanceof User && $user->isAdmin();
        $customPriceCents = null;

        if ($isAdmin && $request->filled('custom_unit_price')) {
            $customPriceCents = Money::toCents($request->validated('custom_unit_price'));
        }

        $cart->add(
            product: $product,
            quantity: (int) $request->validated('quantity'),
            customUnitPriceCents: $customPriceCents,
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart' => $cart->summary(),
            ]);
        }

        return back()->with('success', $product->name.' added to your cart.');
    }

    public function update(UpdateCartItemRequest $request, Product $product, CartService $cart): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user instanceof User && $user->isAdmin();
        $resetPrice = $isAdmin && $request->boolean('reset_custom_price');
        $customPriceCents = null;

        if ($isAdmin && ! $resetPrice && $request->filled('custom_unit_price')) {
            $customPriceCents = Money::toCents($request->validated('custom_unit_price'));
        }

        $cart->update(
            product: $product,
            quantity: (int) $request->validated('quantity'),
            customUnitPriceCents: $customPriceCents,
            resetCustomPrice: $resetPrice,
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart' => $cart->summary(),
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, Product $product, CartService $cart): RedirectResponse|JsonResponse
    {
        $cart->remove($product);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart' => $cart->summary(),
            ]);
        }

        return back()->with('success', $product->name.' removed from your cart.');
    }
}
