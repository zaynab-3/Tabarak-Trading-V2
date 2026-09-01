<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_renders_published_catalogue_data(): void
    {
        Product::factory()->create(['name' => 'Roasted Almond Pouch']);
        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Storefront/Home')
            ->where('newProducts.0.name', 'Roasted Almond Pouch'));
        $this->get('/shop?search=Almond')->assertInertia(fn (Assert $page) => $page
            ->component('Storefront/Shop')
            ->has('products.data', 1));
    }
}
