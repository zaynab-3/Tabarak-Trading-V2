<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ImportBatchStatus;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ImportBatch;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderDeletionNotice;
use App\Models\Product;
use App\Services\Orders\OrderPresenter;
use App\Services\Products\ProductPresenter;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(ProductPresenter $presenter, OrderPresenter $orders): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'products' => Product::query()->count(),
                'published' => Product::query()->where('status', ProductStatus::Published)->count(),
                'categories' => Category::query()->count(),
                'brands' => Brand::query()->count(),
                'media' => Media::query()->count(),
                'importsAwaitingReview' => ImportBatch::query()->where('status', ImportBatchStatus::Review)->count(),
                'pendingOrders' => Order::query()->where('status', 'pending')->count(),
                'orderNotices' => OrderDeletionNotice::query()->count(),
            ],
            'recentProducts' => Product::query()->with(['brand:id,name', 'category:id,name', 'primaryImage.media'])->latest()->limit(6)->get()->map(fn ($product) => $presenter->summary($product)),
            'recentImports' => ImportBatch::query()->with('creator:id,name')->latest()->limit(5)->get(),
            'recentOrders' => Order::query()->withSum('items as items_count', 'quantity')->withSum('items as reserved_stock_quantity', 'stock_reserved')->latest('submitted_at')->limit(5)->get()->map(fn ($order) => $orders->summary($order)),
        ]);
    }
}
