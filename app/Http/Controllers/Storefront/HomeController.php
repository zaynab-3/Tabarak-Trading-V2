<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\Products\ProductPresenter;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(ProductPresenter $presenter): Response
    {
        $categories = Category::query()->where('is_active', true)->whereNull('parent_id')
            ->with('image')->withCount(['products' => fn ($query) => $query->published()])
            ->orderBy('sort_order')->limit(8)->get();
        $featured = Product::query()->published()->where('is_featured', true)
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'primaryImage.media'])
            ->latest('published_at')->limit(8)->get()->map(fn ($product) => $presenter->summary($product));
        $newProducts = Product::query()->published()
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'primaryImage.media'])
            ->latest('published_at')->limit(8)->get()->map(fn ($product) => $presenter->summary($product));

        return Inertia::render('Storefront/Home', [
            'categories' => $categories,
            'featuredProducts' => $featured,
            'newProducts' => $newProducts,
            'brands' => Brand::query()->where('is_active', true)->with('logo')->orderBy('name')->limit(10)->get(),
            'catalogueIntro' => Setting::valueFor('catalogue_intro', 'A dependable wholesale range, selected for stores and food businesses across Lebanon.'),
        ]);
    }
}
