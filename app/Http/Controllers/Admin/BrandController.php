<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brands\StoreBrandRequest;
use App\Http\Requests\Brands\UpdateBrandRequest;
use App\Models\Brand;
use App\Models\Media;
use App\Services\Products\UniqueSlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Brand::class);

        return Inertia::render('Admin/Brands/Index', [
            'brands' => Brand::query()->with('logo')->withCount('products')->orderBy('name')->paginate(20),
            'media' => Media::query()->latest()->limit(100)->get(),
        ]);
    }

    public function store(StoreBrandRequest $request, UniqueSlugGenerator $slugs): RedirectResponse
    {
        Gate::authorize('create', Brand::class);
        $data = $request->validated();
        Brand::query()->create([...$data, 'slug' => $slugs->generate(Brand::class, $data['name'])]);

        return back()->with('success', 'Brand created.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand, UniqueSlugGenerator $slugs): RedirectResponse
    {
        Gate::authorize('update', $brand);
        $data = $request->validated();
        $brand->update([...$data, 'slug' => $slugs->generate(Brand::class, $data['name'], $brand->id)]);

        return back()->with('success', 'Brand updated.');
    }

    public function toggle(Brand $brand): RedirectResponse
    {
        Gate::authorize('update', $brand);
        $brand->update(['is_active' => ! $brand->is_active]);

        return back()->with('success', 'Brand status updated.');
    }
}
