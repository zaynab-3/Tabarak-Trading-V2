<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Media;
use App\Services\Products\UniqueSlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Category::class);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => Category::query()->with(['parent:id,name', 'image'])->withCount('products')->orderBy('sort_order')->paginate(20),
            'parents' => Category::query()->orderBy('sort_order')->get(['id', 'name']),
            'media' => Media::query()->latest()->limit(100)->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request, UniqueSlugGenerator $slugs): RedirectResponse
    {
        Gate::authorize('create', Category::class);
        $data = $request->validated();
        Category::query()->create([...$data, 'slug' => $slugs->generate(Category::class, $data['name'])]);

        return back()->with('success', 'Category created.');
    }

    public function update(UpdateCategoryRequest $request, Category $category, UniqueSlugGenerator $slugs): RedirectResponse
    {
        Gate::authorize('update', $category);
        $data = $request->validated();
        $category->update([...$data, 'slug' => $slugs->generate(Category::class, $data['name'], $category->id)]);

        return back()->with('success', 'Category updated.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', 'Category status updated.');
    }
}
