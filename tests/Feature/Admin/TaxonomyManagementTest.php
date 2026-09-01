<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxonomyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_category(): void
    {
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.categories.store'), [
            'name' => 'Nuts', 'sort_order' => 10, 'is_active' => true,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['name' => 'Nuts', 'slug' => 'nuts']);
    }

    public function test_admin_can_create_a_brand(): void
    {
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.brands.store'), [
            'name' => 'Cedar Grove', 'is_active' => true,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('brands', ['name' => 'Cedar Grove', 'slug' => 'cedar-grove']);
    }
}
