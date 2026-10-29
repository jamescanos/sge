<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        return User::factory()->create()->assignRole($role);
    }

    public function test_admin_can_create_and_update_categories(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Redes',
            'active' => '1',
        ])->assertRedirect(route('categories.index'));

        $category = Category::where('name', 'Redes')->firstOrFail();

        $this->actingAs($admin)->put(route('categories.update', $category), [
            'name' => 'Redes y conectividad',
            'active' => '1',
        ])->assertRedirect(route('categories.index'));

        $this->assertSame('Redes y conectividad', $category->refresh()->name);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Accesorios']);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('categories.store'), ['name' => 'Accesorios'])
            ->assertSessionHasErrors('name');
    }

    public function test_deleting_a_category_is_a_soft_delete_and_can_be_restored(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();

        $this->actingAs($admin)->delete(route('categories.destroy', $category))->assertRedirect(route('categories.index'));
        $this->assertSoftDeleted($category);

        $this->actingAs($admin)->patch(route('categories.restore', $category))->assertRedirect();
        $this->assertNotSoftDeleted($category);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('categories.destroy', $product->category))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($product->category);
    }

    public function test_almacenista_can_view_but_not_create_categories(): void
    {
        $almacenista = $this->userWithRole('almacenista');

        $this->actingAs($almacenista)->get(route('categories.index'))->assertOk();
        $this->actingAs($almacenista)->post(route('categories.store'), ['name' => 'Nueva'])->assertForbidden();
    }

    public function test_vendedor_cannot_access_categories(): void
    {
        $this->actingAs($this->userWithRole('vendedor'))->get(route('categories.index'))->assertForbidden();
    }
}
