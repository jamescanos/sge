<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\User;
use Database\Seeders\CompanySettingSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Guía de configuración visual: identidad corporativa (branding), menú lateral y gráficos del Dashboard.
 */
class ConfiguracionVisualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, CompanySettingSeeder::class]);
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'trade_name' => 'Ferretería El Tornillo',
            'legal_name' => 'Ferretería El Tornillo S.A.S.',
            'nit' => '900.111.222-3',
            'slogan' => 'Todo para su obra',
            'city' => 'Cartago',
            'phone' => '602 111 2233',
            'email' => 'ventas@eltornillo.test',
            'website' => 'https://www.eltornillo.test',
            'primary_color' => '#1d4ed8',
        ], $overrides);
    }

    public function test_el_menu_y_el_login_muestran_la_identidad_corporativa(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->get('/login')->assertOk()->assertSee('Distribuidora Tecnológica')->assertSee('901.234.567-8');

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()
            ->assertSee('Distribuidora Tecnológica')
            ->assertSee(route('settings.branding.edit'))
            ->assertSee('--brand: 15 118 110', false); // color de marca por defecto (#0f766e)
    }

    public function test_el_administrador_actualiza_la_identidad_con_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)
            ->put(route('settings.branding.update'), $this->data(['logo' => UploadedFile::fake()->image('logo.png', 200, 200)]))
            ->assertRedirect(route('settings.branding.edit'))
            ->assertSessionHas('success');

        $company = CompanySetting::first();
        $this->assertSame('Ferretería El Tornillo', $company->trade_name);
        $this->assertSame('#1d4ed8', $company->primary_color);
        Storage::disk('public')->assertExists($company->logo_path);

        // El nuevo nombre y el nuevo color se ven en todas las páginas
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertSee('Ferretería El Tornillo')
            ->assertSee('--brand: 29 78 216', false)
            ->assertSee('storage/'.$company->logo_path);

        // Quitar el logo borra el archivo
        $this->actingAs($admin)->put(route('settings.branding.update'), $this->data(['remove_logo' => '1']));
        Storage::disk('public')->assertMissing($company->logo_path);
        $this->assertNull($company->fresh()->logo_path);

        // El cambio quedó en la auditoría (Clase 12)
        $this->assertDatabaseHas('activity_log', ['log_name' => 'branding', 'event' => 'updated']);
    }

    public function test_validaciones_de_la_identidad_corporativa(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)
            ->put(route('settings.branding.update'), $this->data([
                'nit' => 'ABC', 'primary_color' => 'azul', 'trade_name' => '',
                'logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
            ]))
            ->assertSessionHasErrors(['nit', 'primary_color', 'trade_name', 'logo']);

        $this->assertSame('Distribuidora Tecnológica', CompanySetting::first()->trade_name);
    }

    public function test_solo_quien_tiene_permiso_ve_o_edita_la_identidad(): void
    {
        $vendedor = User::factory()->create()->assignRole('vendedor');

        $this->actingAs($vendedor)->get(route('settings.branding.edit'))->assertForbidden();
        $this->actingAs($vendedor)->put(route('settings.branding.update'), $this->data())->assertForbidden();
        $this->actingAs($vendedor)->get(route('dashboard'))->assertDontSee(route('settings.branding.edit'));
    }

    public function test_el_dashboard_muestra_graficos_segun_los_permisos(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $vendedor = User::factory()->create()->assignRole('vendedor');

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()
            ->assertSee('Ventas vs. compras')
            ->assertSee('Inventario por categoría')
            ->assertSee('Órdenes de compra');

        // El vendedor no ve compras: no aparece el gráfico de órdenes de compra
        $this->actingAs($vendedor)->get(route('dashboard'))->assertOk()
            ->assertSee('Ventas diarias')
            ->assertDontSee('Órdenes de compra');
    }
}
