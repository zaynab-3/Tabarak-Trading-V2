<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Media\AttachProductImages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\UploadProductImagesRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProductImageController extends Controller
{
    public function store(UploadProductImagesRequest $request, Product $product, AttachProductImages $action): RedirectResponse
    {
        Gate::authorize('update', $product);
        $action->handle($product, $request->file('images'));

        return back()->with('success', 'Product images uploaded.');
    }

    public function destroy(Product $product, ProductImage $productImage): RedirectResponse
    {
        Gate::authorize('update', $product);
        abort_unless($productImage->product_id === $product->id, 404);
        $wasPrimary = $productImage->is_primary;
        $productImage->delete();
        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Product image removed.');
    }

    public function primary(Product $product, ProductImage $productImage): RedirectResponse
    {
        Gate::authorize('update', $product);
        abort_unless($productImage->product_id === $product->id, 404);
        DB::transaction(function () use ($product, $productImage) {
            $product->images()->update(['is_primary' => false]);
            $productImage->update(['is_primary' => true]);
        });

        return back()->with('success', 'Primary image updated.');
    }
}
