<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Products\ArchiveProduct;
use App\Actions\Products\CreateProduct;
use App\Actions\Products\DeleteProduct;
use App\Actions\Products\UpdateProduct;
use App\DTOs\ProductData;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\ProductIndexRequest;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Products\ProductPresenter;
use App\Services\Products\ProductQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request, ProductQueryService $queries, ProductPresenter $presenter): Response
    {
        Gate::authorize('viewAny', Product::class);
        $filters = $request->validated();

        return Inertia::render('Admin/Products/Index', [
            'products' => $queries->admin($filters)->latest()->paginate(15)->withQueryString()->through(fn ($product) => $presenter->summary($product)),
            'filters' => $filters,
            ...$this->formOptions(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Product::class);

        return Inertia::render('Admin/Products/Create', $this->formOptions());
    }

    public function store(StoreProductRequest $request, CreateProduct $action): RedirectResponse
    {
        Gate::authorize('create', Product::class);
        $product = $action->handle(ProductData::fromArray($request->validated()));

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created successfully.');
    }

    public function edit(Product $product, ProductPresenter $presenter): Response
    {
        Gate::authorize('update', $product);
        $product->load(['brand', 'category', 'images.media', 'primaryImage.media', 'variants']);

        return Inertia::render('Admin/Products/Edit', ['product' => $presenter->detail($product), ...$this->formOptions()]);
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProduct $action): RedirectResponse
    {
        Gate::authorize('update', $product);
        $action->handle($product, ProductData::fromArray($request->validated()));

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function archive(Product $product, ArchiveProduct $action): RedirectResponse
    {
        Gate::authorize('delete', $product);
        $action->handle($product);

        return back()->with('success', 'Product archived.');
    }

    public function restore(Product $product): RedirectResponse
    {
        Gate::authorize('restore', $product);
        $product->update(['status' => ProductStatus::Draft, 'published_at' => null]);

        return back()->with('success', 'Product restored as a draft.');
    }

    public function destroy(Product $product, DeleteProduct $action): RedirectResponse
    {
        Gate::authorize('delete', $product);
        $name = $product->name;
        $action->handle($product);

        return back()->with('success', "$name permanently deleted.");
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'categories' => Category::query()->orderBy('sort_order')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => array_map(fn ($status) => $status->value, ProductStatus::cases()),
        ];
    }
}
