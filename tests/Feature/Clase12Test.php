<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Clase 12: auditoría (activitylog) y kardex de inventario.
 */
class Clase12Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DatabaseSeeder::class);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function assertKardexCuadra(Product $product): void
    {
        $product->refresh();
        $last = $product->movements()->latest('id')->value('balance');
        $this->assertSame($product->stock, $last, "El kardex de {$product->name} no cuadra con el stock");
        $in = (int) $product->movements()->where('type', 'entrada')->sum('quantity');
        $out = (int) $product->movements()->where('type', 'salida')->sum('quantity');
        $this->assertSame($product->stock, $in - $out);
    }

    public function test_seeder_crea_inventario_inicial(): void
    {
        foreach (Product::all() as $product) {
            $this->assertSame(1, $product->movements()->count());
            $this->assertSame('inventario_inicial', $product->movements()->first()->concept);
            $this->assertKardexCuadra($product);
        }
    }

    public function test_venta_anulacion_y_compra_generan_movimientos(): void
    {
        $admin = $this->user('admin@test.com');
        $laptop = Product::where('name', 'Laptop Lenovo')->first();

        // Venta: salida
        $this->actingAs($admin)->post('/sales', [
            'client_id' => Client::first()->id, 'sale_date' => now()->format('Y-m-d'),
            'items' => [['product_id' => $laptop->id, 'quantity' => 3]],
        ])->assertRedirect();
        $sale = Sale::latest('id')->first();
        $m = $laptop->movements()->latest('id')->first();
        $this->assertSame(['salida', 'venta', 3, 7], [$m->type, $m->concept, $m->quantity, $m->balance]);
        $this->assertSame($sale->invoice_number, $m->referenceLabel());
        $this->assertSame($admin->id, $m->user_id);

        // Venta sin stock: no crea movimiento
        $before = InventoryMovement::count();
        $this->actingAs($admin)->post('/sales', [
            'client_id' => Client::first()->id, 'sale_date' => now()->format('Y-m-d'),
            'items' => [['product_id' => $laptop->id, 'quantity' => 999]],
        ])->assertSessionHasErrors('items');
        $this->assertSame($before, InventoryMovement::count());

        // Anulación: entrada
        $this->actingAs($admin)->delete("/sales/{$sale->id}")->assertRedirect();
        $m = $laptop->movements()->latest('id')->first();
        $this->assertSame(['entrada', 'anulacion_venta', 3, 10], [$m->type, $m->concept, $m->quantity, $m->balance]);
        // La venta anulada queda en la papelera, pero el kardex conserva su número (salida y entrada)
        $this->assertSame($sale->invoice_number, $m->referenceLabel());
        $this->get('/inventario/kardex?product='.$laptop->id)->assertOk()->assertSeeInOrder([$sale->invoice_number, $sale->invoice_number]);

        // Compra recibida parcialmente: entrada con costo
        $this->actingAs($admin)->post('/purchases', [
            'provider_id' => Provider::first()->id, 'purchase_date' => now()->format('Y-m-d'),
            'items' => [['product_id' => $laptop->id, 'quantity' => 5, 'unit_cost' => 3200000]],
        ]);
        $purchase = Purchase::latest('id')->first();
        $detail = $purchase->details()->first();
        $this->actingAs($admin)->patch("/purchases/{$purchase->id}/receive", ['items' => [$detail->id => 2]])->assertRedirect();
        $m = $laptop->movements()->latest('id')->first();
        $this->assertSame(['entrada', 'compra', 2, 12], [$m->type, $m->concept, $m->quantity, $m->balance]);
        $this->assertEquals(3200000, (float) $m->unit_cost);
        $this->assertSame($purchase->purchase_number, $m->referenceLabel());

        $this->assertKardexCuadra($laptop);

        // Pantallas
        $this->actingAs($admin)->get('/inventario/kardex?product='.$laptop->id)->assertOk()
            ->assertSee('Anulación de venta')->assertSee($purchase->purchase_number)->assertSee('coincide con el stock');
        $this->actingAs($admin)->get("/inventario/kardex/{$laptop->id}/pdf")->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_ajuste_de_inventario(): void
    {
        $alm = $this->user('almacenista@test.com');
        $mouse = Product::where('name', 'Mouse Logitech')->first(); // stock 30

        $this->actingAs($alm)->get("/inventario/ajustes/{$mouse->id}")->assertOk();

        // Motivo obligatorio
        $this->actingAs($alm)->post("/inventario/ajustes/{$mouse->id}", ['counted_stock' => 28])
            ->assertSessionHasErrors('reason');

        // Sin diferencia
        $this->actingAs($alm)->post("/inventario/ajustes/{$mouse->id}", ['counted_stock' => 30, 'reason' => 'Conteo mensual sin novedades'])
            ->assertSessionHas('error');

        $this->actingAs($alm)->post("/inventario/ajustes/{$mouse->id}", ['counted_stock' => 28, 'reason' => 'Conteo: 2 unidades dañadas'])
            ->assertRedirect();
        $m = $mouse->movements()->latest('id')->first();
        $this->assertSame(['salida', 'ajuste', 2, 28, 'Conteo: 2 unidades dañadas'], [$m->type, $m->concept, $m->quantity, $m->balance, $m->notes]);

        $this->actingAs($alm)->post("/inventario/ajustes/{$mouse->id}", ['counted_stock' => 31, 'reason' => 'Se encontraron 3 unidades en vitrina'])
            ->assertRedirect();
        $this->assertSame(31, $mouse->fresh()->stock);
        $this->assertKardexCuadra($mouse);

        // El vendedor no puede ajustar ni ver kardex
        $ven = $this->user('vendedor@test.com');
        $this->actingAs($ven)->get("/inventario/ajustes/{$mouse->id}")->assertForbidden();
        $this->actingAs($ven)->get('/inventario/kardex')->assertForbidden();
    }

    public function test_el_stock_ya_no_se_edita_en_el_crud(): void
    {
        $admin = $this->user('admin@test.com');
        $p = Product::where('name', 'Monitor Samsung')->first(); // stock 4

        $this->actingAs($admin)->get("/products/{$p->id}/edit")->assertOk()->assertSee('Ajustar inventario')->assertDontSee('name="stock"', false);
        $this->actingAs($admin)->put("/products/{$p->id}", [
            'name' => 'Monitor Samsung 27', 'price' => 900000, 'stock' => 999,
            'category_id' => $p->category_id, 'active' => '1',
        ])->assertRedirect();
        $this->assertSame(4, $p->fresh()->stock);

        // Crear con stock inicial → movimiento inicial
        $this->actingAs($admin)->post('/products', [
            'name' => 'Webcam', 'price' => 200000, 'stock' => 6, 'category_id' => $p->category_id, 'active' => '1',
        ])->assertRedirect();
        $new = Product::where('name', 'Webcam')->first();
        $this->assertSame('inventario_inicial', $new->movements()->first()->concept);
        $this->assertKardexCuadra($new);
    }

    public function test_auditoria_registra_cambios_con_antes_y_despues(): void
    {
        $admin = $this->user('admin@test.com');
        $p = Product::where('name', 'Monitor Samsung')->first();

        $this->actingAs($admin)->put("/products/{$p->id}", [
            'name' => 'Monitor Samsung', 'price' => 799000, 'category_id' => $p->category_id, 'active' => '1',
        ]);

        $log = Activity::where('log_name', 'productos')->where('event', 'updated')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame($admin->id, $log->causer_id);
        $this->assertSame('actualizado', $log->description);
        $this->assertEquals(799000, $log->attribute_changes['attributes']['price']);
        $this->assertEquals(850000, $log->attribute_changes['old']['price']);
        $this->assertArrayNotHasKey('name', $log->attribute_changes['attributes'], 'solo lo que cambió');

        // Los movimientos de stock NO generan ruido en la auditoría del producto
        $count = Activity::where('log_name', 'productos')->count();
        $this->actingAs($this->user('almacenista@test.com'))->post("/inventario/ajustes/{$p->id}", ['counted_stock' => 1, 'reason' => 'Conteo mensual de prueba']);
        $this->assertSame($count, Activity::where('log_name', 'productos')->count());

        // Seguridad: cambio de roles queda registrado; la contraseña nunca
        $ven = $this->user('vendedor@test.com');
        $this->actingAs($admin)->put(route('settings.users.update', $ven), [
            'name' => $ven->name, 'email' => $ven->email, 'active' => '1', 'roles' => ['vendedor', 'almacenista'],
            'password' => 'NuevaClave123', 'password_confirmation' => 'NuevaClave123',
        ])->assertRedirect();
        $sec = Activity::where('log_name', 'seguridad')->latest('id')->first();
        $this->assertSame(['almacenista', 'vendedor'], $sec->attribute_changes['attributes']['roles']);
        $this->assertSame(['vendedor'], $sec->attribute_changes['old']['roles']);
        $this->assertSame(0, Activity::where('attribute_changes', 'like', '%password%')->count());

        // Eliminar cliente → evento eliminado
        $client = Client::create(['document_type' => 'CC', 'document_number' => '123123', 'name' => 'Temporal', 'active' => true]);
        $this->actingAs($admin)->delete("/clients/{$client->id}");
        $this->assertSame(1, Activity::where('log_name', 'clientes')->where('event', 'deleted')->count());

        // Pantalla de auditoría
        $this->actingAs($admin)->get('/auditoria')->assertOk()->assertSee('seguridad')->assertSee('799000');
        $this->actingAs($admin)->get('/auditoria?module=productos&event=updated&user='.$admin->id)->assertOk()->assertSee('850000');
        $this->actingAs($this->user('almacenista@test.com'))->get('/auditoria')->assertForbidden();
    }

    public function test_comando_inicializar_kardex(): void
    {
        $p = Product::where('name', 'Diadema HyperX')->first();
        $p->movements()->delete();

        $this->artisan('kardex:inicializar')->expectsOutputToContain('Productos inicializados en el kardex: 1')->assertSuccessful();
        $this->assertKardexCuadra($p);
    }
}
