<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_creation_requires_valid_data(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), ['status' => 'published'])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_create_products_with_unique_server_generated_slugs(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $payload = ['name' => 'Sea Salt Cashews', 'category_id' => $category->id, 'status' => 'published', 'is_featured' => true];

        $this->actingAs($admin)->post(route('admin.products.store'), $payload)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.products.store'), $payload)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['slug' => 'sea-salt-cashews']);
        $this->assertDatabaseHas('products', ['slug' => 'sea-salt-cashews-2']);
    }

    public function test_products_are_filtered_on_the_server(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->create(['name' => 'Coffee Beans']);
        Product::factory()->create(['name' => 'Roasted Almonds']);

        $this->actingAs($admin)->get(route('admin.products.index', ['search' => 'Coffee']))
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Products/Index')->has('products.data', 1)->where('products.data.0.name', 'Coffee Beans'));
    }

    public function test_regular_user_cannot_create_a_product(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.products.store'), ['name' => 'Blocked', 'status' => 'draft'])
            ->assertForbidden();
    }
}
