<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Products\ProductPresenter;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __invoke(Product $product, ProductPresenter $presenter): Response
    {
        abort_unless($product->status === ProductStatus::Published && $product->published_at?->isPast(), 404);
        $product->load(['brand:id,name,slug', 'category:id,name,slug', 'images.media', 'primaryImage.media', 'variants']);
        $related = Product::query()->published()->whereKeyNot($product->id)->where('category_id', $product->category_id)
            ->with(['brand:id,name,slug', 'category:id,name,slug', 'primaryImage.media'])->limit(4)->get()
            ->map(fn ($item) => $presenter->summary($item));

        return Inertia::render('Storefront/ProductShow', ['product' => $presenter->detail($product), 'relatedProducts' => $related]);
    }
}
