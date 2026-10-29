<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
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

    public function test_admin_can_list_and_create_products(): void
    {
        $admin = $this->userWithRole('admin');
        $category = Category::factory()->create();

        $this->actingAs($admin)->get(route('products.index'))->assertOk();

        $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Teclado mecánico',
            'price' => '150.50',
            'stock' => 10,
            'category_id' => $category->id,
            'active' => '1',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Teclado mecánico', 'stock' => 10, 'active' => true]);
    }

    public function test_admin_can_update_a_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->userWithRole('admin'))->put(route('products.update', $product), [
            'name' => 'Nombre nuevo',
            'price' => 99,
            'stock' => 3,
            'category_id' => $product->category_id,
        ])->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertSame('Nombre nuevo', $product->name);
        $this->assertFalse($product->active);
    }

    public function test_deleting_a_product_is_a_soft_delete_and_can_be_restored(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();

        $this->actingAs($admin)->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));
        $this->assertSoftDeleted($product);

        $this->actingAs($admin)->get(route('products.index', ['trashed' => 1]))->assertOk()->assertSee($product->name);

        $this->actingAs($admin)->patch(route('products.restore', $product))->assertRedirect();
        $this->assertNotSoftDeleted($product);
    }

    public function test_product_cannot_be_restored_while_its_category_is_trashed(): void
    {
        $product = Product::factory()->create();
        $product->delete();
        $product->category->delete();

        $this->actingAs($this->userWithRole('admin'))
            ->patch(route('products.restore', $product))
            ->assertSessionHas('error');

        $this->assertSoftDeleted($product);
    }

    public function test_product_cannot_use_a_trashed_category(): void
    {
        $category = Category::factory()->create();
        $category->delete();

        $this->actingAs($this->userWithRole('admin'))->post(route('products.store'), [
            'name' => 'Mouse',
            'price' => 10,
            'stock' => 1,
            'category_id' => $category->id,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_vendedor_can_view_but_not_modify_products(): void
    {
        $vendedor = $this->userWithRole('vendedor');
        $product = Product::factory()->create();

        $this->actingAs($vendedor)->get(route('products.index'))
            ->assertOk()
            ->assertDontSee(route('products.create'))
            ->assertDontSee(route('products.edit', $product));

        $this->actingAs($vendedor)->get(route('products.create'))->assertForbidden();
        $this->actingAs($vendedor)->delete(route('products.destroy', $product))->assertForbidden();
        $this->assertNotSoftDeleted($product);
    }

    public function test_almacenista_can_edit_but_not_delete_products(): void
    {
        $almacenista = $this->userWithRole('almacenista');
        $product = Product::factory()->create();

        $this->actingAs($almacenista)->get(route('products.edit', $product))->assertOk();
        $this->actingAs($almacenista)->delete(route('products.destroy', $product))->assertForbidden();
        $this->actingAs($almacenista)->patch(route('products.restore', $product))->assertForbidden();
    }

    public function test_user_without_role_cannot_access_products(): void
    {
        $this->actingAs(User::factory()->create())->get(route('products.index'))->assertForbidden();
    }
}
