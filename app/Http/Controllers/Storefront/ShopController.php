<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ShopIndexRequest;
use App\Models\Category;
use App\Services\Products\ProductPresenter;
use App\Services\Products\ProductQueryService;
use App\Services\Storefront\StorefrontBrandOptions;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(
        ShopIndexRequest $request,
        ProductQueryService $queries,
        ProductPresenter $presenter,
        StorefrontBrandOptions $brandOptions,
    ): Response {
        $filters = $request->validated();
        $brands = $brandOptions->forCategory($filters['category'] ?? null);

        if (isset($filters['brand']) && ! $brands->contains('id', (int) $filters['brand'])) {
            unset($filters['brand']);
        }

        $query = $queries->storefront($filters);

        match ($filters['sort'] ?? 'newest') {
            'name-asc' => $query->orderBy('name'),
            'name-desc' => $query->orderByDesc('name'),
            default => $query->latest('published_at'),
        };

        return Inertia::render('Storefront/Shop', [
            'products' => $query->paginate(12)->withQueryString()->through(fn ($product) => $presenter->summary($product)),
            'filters' => $filters,
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']),
            'brands' => $brands,
        ]);
    }
}
