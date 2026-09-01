<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Orders\CompleteOrder;
use App\Actions\Orders\DeleteOrder;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderIndexRequest;
use App\Models\Order;
use App\Services\Orders\OrderPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(OrderIndexRequest $request, OrderPresenter $presenter): Response
    {
        Gate::authorize('viewAny', Order::class);
        $filters = $request->validated();
        $orders = Order::query()
            ->withSum('items as items_count', 'quantity')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$search.'%');
                });
            })
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Order $order) => $presenter->summary($order));

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => array_map(fn (OrderStatus $status) => $status->value, OrderStatus::cases()),
        ]);
    }

    public function show(Order $order, OrderPresenter $presenter): Response
    {
        Gate::authorize('view', $order);
        $order->load('items');

        return Inertia::render('Admin/Orders/Show', ['order' => $presenter->detail($order)]);
    }

    public function complete(Order $order, CompleteOrder $action): RedirectResponse
    {
        Gate::authorize('update', $order);
        $action->handle($order);

        return back()->with('success', $order->order_number.' marked completed.');
    }

    public function destroy(Order $order, DeleteOrder $action): RedirectResponse
    {
        Gate::authorize('delete', $order);
        $number = $order->order_number;
        $action->handle($order);

        return redirect()->route('admin.orders.index')->with('success', $number.' deleted.');
    }
}
