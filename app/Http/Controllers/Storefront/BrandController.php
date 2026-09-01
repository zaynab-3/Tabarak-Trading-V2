<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\Products\ProductPresenter;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Storefront/Brands', [
            'brands' => Brand::query()->where('is_active', true)->with('logo')
                ->withCount(['products' => fn ($query) => $query->published()])->orderBy('name')->paginate(24),
        ]);
    }

    public function show(Brand $brand, ProductPresenter $presenter): Response
    {
        abort_unless($brand->is_active, 404);
        $products = $brand->products()->published()->with(['brand:id,name,slug', 'category:id,name,slug', 'primaryImage.media'])
            ->latest('published_at')->paginate(12)->through(fn ($product) => $presenter->summary($product));

        return Inertia::render('Storefront/BrandShow', ['brand' => $brand->load('logo'), 'products' => $products]);
    }
}
