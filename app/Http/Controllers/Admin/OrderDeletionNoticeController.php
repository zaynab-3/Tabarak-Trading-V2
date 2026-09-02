<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderDeletionNotice;
use App\Services\Orders\OrderDeletionNoticePresenter;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrderDeletionNoticeController extends Controller
{
    public function index(OrderDeletionNoticePresenter $presenter): Response
    {
        Gate::authorize('viewAny', OrderDeletionNotice::class);

        return Inertia::render('Admin/OrderNotices/Index', [
            'notices' => OrderDeletionNotice::query()
                ->with('deletedBy:id,name')
                ->latest('recorded_at')
                ->paginate(20)
                ->through(fn (OrderDeletionNotice $notice) => $presenter->summary($notice)),
        ]);
    }

    public function show(OrderDeletionNotice $orderNotice, OrderDeletionNoticePresenter $presenter): Response
    {
        Gate::authorize('view', $orderNotice);
        $orderNotice->load('deletedBy:id,name');

        return Inertia::render('Admin/OrderNotices/Show', [
            'notice' => $presenter->detail($orderNotice),
        ]);
    }
}
