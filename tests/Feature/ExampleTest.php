<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_shop(): void
    {
        $this->get('/')
            ->assertRedirect('/shop');
    }

    public function test_storefront_renders_published_catalogue_data(): void
    {
        Product::factory()->create([
            'name' => 'Roasted Almond Pouch',
        ]);

        $this->get('/shop')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Storefront/Shop')
                    ->has('products.data', 1)
                    ->where('products.data.0.name', 'Roasted Almond Pouch')
            );

        $this->get('/shop?search=Almond')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Storefront/Shop')
                    ->has('products.data', 1)
                    ->where('products.data.0.name', 'Roasted Almond Pouch')
            );
    }
}
