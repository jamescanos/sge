<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_sees_every_module(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('products.index'))
            ->assertSee(route('categories.index'))
            ->assertSee(route('products.create'));
    }

    public function test_vendedor_only_sees_products_in_read_only_mode(): void
    {
        $vendedor = User::factory()->create()->assignRole('vendedor');

        $this->actingAs($vendedor)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('products.index'))
            ->assertDontSee(route('categories.index'))
            ->assertDontSee(route('products.create'));
    }

    public function test_user_without_role_sees_no_modules(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No tienes módulos disponibles.')
            ->assertSee('Sin rol asignado')
            ->assertDontSee(route('products.index'));
    }
}
