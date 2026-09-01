<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ImportBatchStatus;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ImportBatch;
use App\Models\Media;
use App\Models\Product;
use App\Services\Products\ProductPresenter;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(ProductPresenter $presenter): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'products' => Product::query()->count(),
                'published' => Product::query()->where('status', ProductStatus::Published)->count(),
                'categories' => Category::query()->count(),
                'brands' => Brand::query()->count(),
                'media' => Media::query()->count(),
                'importsAwaitingReview' => ImportBatch::query()->where('status', ImportBatchStatus::Review)->count(),
            ],
            'recentProducts' => Product::query()->with(['brand:id,name', 'category:id,name', 'primaryImage.media'])->latest()->limit(6)->get()->map(fn ($product) => $presenter->summary($product)),
            'recentImports' => ImportBatch::query()->with('creator:id,name')->latest()->limit(5)->get(),
        ]);
    }
}
