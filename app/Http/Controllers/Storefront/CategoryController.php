<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Products\ProductPresenter;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Storefront/Categories', [
            'categories' => Category::query()->where('is_active', true)->with('image')
                ->withCount(['products' => fn ($query) => $query->published()])->orderBy('sort_order')->paginate(18),
        ]);
    }

    public function show(Category $category, ProductPresenter $presenter): Response
    {
        abort_unless($category->is_active, 404);
        $products = $category->products()->published()->with(['brand:id,name,slug', 'category:id,name,slug', 'primaryImage.media'])
            ->latest('published_at')->paginate(12)->through(fn ($product) => $presenter->summary($product));

        return Inertia::render('Storefront/CategoryShow', ['category' => $category->load('image'), 'products' => $products]);
    }
}
