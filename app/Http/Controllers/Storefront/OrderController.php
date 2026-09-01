<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Orders\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOrderRequest;
use App\Models\Order;
use App\Services\Orders\InvoicePdf;
use App\Services\Orders\OrderPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, PlaceOrder $action): RedirectResponse
    {
        $order = $action->handle(
            customerName: trim((string) $request->validated('customer_name')),
            customerPhone: (string) $request->validated('customer_phone'),
        );

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order sent to Tabarak Trading.');
    }

    public function show(Order $order, OrderPresenter $presenter): Response
    {
        $order->load('items');

        return Inertia::render('Storefront/OrderShow', ['order' => $presenter->detail($order)]);
    }

    public function invoice(Order $order, InvoicePdf $invoice): SymfonyResponse
    {
        return $invoice->make($order)->download($order->order_number.'.pdf');
    }
}
