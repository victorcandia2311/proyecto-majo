<?php

use App\Models\Asistencia;
use App\Models\DetalleVenta;
use App\Models\Empleado;
use App\Models\EmpleadoTemporal;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoProveedor;
use App\Models\Proveedor;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.block_store' => 'array']);
    DB::purge('sqlite');

    Schema::create('empleado', function (Blueprint $table) {
        $table->increments('id_empleado');
        $table->string('nombres', 100);
        $table->string('documento_identidad', 20)->unique();
        $table->string('usuario', 50)->nullable()->unique();
        $table->boolean('es_eventual')->nullable()->default(false);
    });

    Schema::create('asistencia', function (Blueprint $table) {
        $table->increments('id_asistencia');
        $table->integer('id_empleado');
        $table->date('fecha');
        $table->time('hora_ingreso')->nullable();
        $table->time('hora_salida')->nullable();
        $table->foreign('id_empleado')->references('id_empleado')->on('empleado')->cascadeOnDelete();
    });

    Schema::create('empleado_temporal', function (Blueprint $table) {
        $table->increments('id_temporal');
        $table->integer('id_empleado')->nullable();
        $table->string('nombre_temp', 100)->nullable();
        $table->date('fecha_inicio')->nullable();
        $table->date('fecha_fin')->nullable();
        $table->time('hora_ingreso')->nullable();
        $table->time('hora_salida')->nullable();
        $table->string('agencia_outsourcing', 100)->nullable();
    });

    Schema::create('producto', function (Blueprint $table) {
        $table->increments('id_producto');
        $table->string('nombre_producto', 150);
        $table->text('descripcion')->nullable();
        $table->string('imagen_path')->nullable();
        $table->integer('stock_actual')->default(0);
        $table->integer('stock_minimo_alerta')->default(10);
        $table->decimal('precio_venta', 10, 2);
        $table->softDeletes();
    });

    Schema::create('proveedor', function (Blueprint $table) {
        $table->increments('id_proveedor');
        $table->string('nombre_empresa', 100);
    });

    Schema::create('producto_proveedor', function (Blueprint $table) {
        $table->integer('id_producto');
        $table->integer('id_proveedor');
        $table->decimal('precio_compra', 10, 2);
        $table->primary(['id_producto', 'id_proveedor']);
        $table->foreign('id_producto')->references('id_producto')->on('producto')->cascadeOnDelete();
        $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedor')->cascadeOnDelete();
    });

    Schema::create('movimiento_inventario', function (Blueprint $table) {
        $table->increments('id_movimiento');
        $table->integer('id_producto');
        $table->string('tipo_movimiento', 20);
        $table->integer('cantidad');
        $table->string('motivo', 200)->nullable();
        $table->timestamp('fecha_hora')->useCurrent();
        $table->foreign('id_producto')->references('id_producto')->on('producto');
    });

    Schema::create('venta', function (Blueprint $table) {
        $table->increments('id_venta');
        $table->timestamp('fecha_hora')->useCurrent();
        $table->string('cliente_nombre', 100)->nullable();
        $table->string('cliente_documento', 20)->nullable();
        $table->string('metodo_pago', 50);
        $table->decimal('total_venta', 10, 2);
    });

    Schema::create('detalle_venta', function (Blueprint $table) {
        $table->increments('id_detalle');
        $table->integer('id_venta');
        $table->integer('id_producto');
        $table->integer('cantidad');
        $table->decimal('precio_unitario', 10, 2);
        $table->foreign('id_venta')->references('id_venta')->on('venta');
        $table->foreign('id_producto')->references('id_producto')->on('producto');
    });
});

afterEach(function () {
    DB::purge('sqlite');
});

beforeEach(function () {
    (require database_path('migrations/2026_09_25_200342_add_authentication_fields_to_empleado_table.php'))->up();
    $this->actingAs(Empleado::factory()->withAccess('admin')->make(['id_empleado' => 2147483647]));
});
test('module routes return their expected view data', function (string $routeName, string $viewName, array $variables) {

    $this->get(route($routeName))->assertOk()->assertViewIs($viewName)->assertViewHasAll($variables);
})->with([
    ['productos.index', 'productos.index', ['productos']],
    ['productos.create', 'productos.crear', []],
    ['proveedores.index', 'proveedores.index', ['proveedores', 'productos', 'precios']],
    ['proveedores.asignar-producto', 'proveedores.asignar_producto', ['proveedores', 'productos']],
    ['empleados.index', 'empleados.index', ['empleados']],
    ['inventario.index', 'inventario.index', ['productos', 'movimientos']],
    ['asistencia.index', 'asistencia.index', ['empleados', 'asistencias', 'empleadosTemporales']],
    ['ventas.index', 'ventas.index', ['productos', 'ventas', 'carrito']],
    ['ventas.nueva', 'ventas.nueva', ['productos', 'carrito', 'totalCarrito']],
    ['dashboard.index', 'dashboard.index', []],
]);

test('administrator views expose the theme selector and all available choices', function () {
    $this->get(route('dashboard.index'))
        ->assertOk()
        ->assertSee('data-theme-selector', false)
        ->assertSee('<option value="light">Claro</option>', false)
        ->assertSee('<option value="dark">Oscuro</option>', false)
        ->assertSee('<option value="auto">Automático</option>', false);
});

test('employee sales screen uses the responsive visual point of sale', function () {
    Producto::factory()->create(['stock_actual' => 4, 'descripcion' => 'Edición especial']);
    $this->actingAs(Empleado::factory()->withAccess('empleado')->make(['id_empleado' => 42]))
        ->get(route('ventas.index'))
        ->assertOk()
        ->assertViewIs('ventas.pos')
        ->assertSee('pos-product-grid', false)
        ->assertSee('Edición especial')
        ->assertSee('pos-checkout-form', false)
        ->assertSee('data-theme-selector', false)
        ->assertSee('<option value="auto">Automático</option>', false);
});

test('employee point of sale renders product images from the public disk', function () {
    config([
        'filesystems.default' => 'local',
        'filesystems.disks.public.url' => 'https://cdn.example.test/product-images',
    ]);
    Producto::factory()->create([
        'stock_actual' => 4,
        'imagen_path' => 'productos/mouse.png',
    ]);

    $this->actingAs(Empleado::factory()->withAccess('empleado')->make(['id_empleado' => 43]))
        ->get(route('ventas.index'))
        ->assertSee('src="https://cdn.example.test/product-images/productos/mouse.png"', false)
        ->assertSee('https:\\/\\/cdn.example.test\\/product-images\\/productos\\/mouse.png', false);
});

test('employee registration and listing do not show employee type', function () {
    $this->get(route('empleados.index'))
        ->assertOk()
        ->assertDontSee('>Tipo<', false)
        ->assertDontSee('es_eventual')
        ->assertDontSee('Permanente')
        ->assertDontSee('Eventual');

    $this->post(route('empleados.store'), [
        'nombres' => 'Ana',
        'documento_identidad' => '00123456',
        'usuario' => 'ana',
    ])->assertRedirectToRoute('empleados.index');

    expect(Empleado::sole()->es_eventual)->toBeFalse();
});

test('inventory history shows fifteen movements per page', function () {
    $producto = Producto::factory()->create();
    MovimientoInventario::factory()->count(16)->for($producto, 'producto')->create();

    $response = $this->get(route('inventario.index'))->assertOk();

    expect($response['movimientos']->count())->toBe(15);
    expect($response['movimientos']->total())->toBe(16);
    expect($response['movimientos']->url(2))->toContain('page=2');
    $response->assertSee('data-async-pagination', false);

    $this->get(route('inventario.index', ['page' => 2]))
        ->assertOk()
        ->assertViewHas('movimientos', fn ($movimientos) => $movimientos->count() === 1);
});

test('sales history paginates fifteen records and totals every sale within the selected dates', function () {
    Venta::factory()->count(16)->create([
        'fecha_hora' => '2026-10-04 12:00:00',
        'total_venta' => '10.00',
    ]);
    Venta::factory()->create([
        'fecha_hora' => '2026-10-06 12:00:00',
        'total_venta' => '500.00',
    ]);

    $response = $this->get(route('ventas.index', [
        'fecha_desde' => '2026-10-01',
        'fecha_hasta' => '2026-10-05',
    ]))->assertOk()->assertSee('Total de ventas del periodo seleccionado');

    expect($response['ventas']->count())->toBe(15);
    expect($response['ventas']->total())->toBe(16);
    expect((float) $response['totalVentasFiltradas'])->toBe(160.0);
    expect($response['ventas']->url(2))->toContain('fecha_desde=2026-10-01');
    expect($response['ventas']->url(2))->toContain('fecha_hasta=2026-10-05');
    $response->assertSee('data-async-pagination', false);

    $this->get(route('ventas.index', [
        'fecha_desde' => '2026-10-01',
        'fecha_hasta' => '2026-10-05',
        'page' => 2,
    ]))->assertOk()
        ->assertViewHas('ventas', fn ($ventas) => $ventas->count() === 1);
});

test('sales history hides the period total when no date filter is selected', function () {
    $this->get(route('ventas.index'))->assertDontSee('Total de ventas del periodo seleccionado');
});

test('sales date filters reject an end date earlier than the start date', function () {
    $this->get(route('ventas.index', [
        'fecha_desde' => '2026-10-05',
        'fecha_hasta' => '2026-10-01',
    ]))->assertSessionHasErrors('fecha_hasta');
});

test('edit and detail routes bind legacy primary keys', function (string $modelClass, string $routeName, string $viewName, string $variable) {
    $model = $modelClass::factory()->create();

    $this->get(route($routeName, $model))->assertOk()->assertViewIs($viewName)
        ->assertViewHas($variable, fn (Model $value): bool => $value->is($model));
})->with([
    [Producto::class, 'productos.edit', 'productos.editar', 'producto'],
    [Empleado::class, 'empleados.edit', 'empleados.editar', 'empleado'],
    [Asistencia::class, 'asistencia.edit', 'asistencia.editar', 'asistencia'],
    [Venta::class, 'ventas.show', 'ventas.detalle', 'venta'],
]);

test('products can be created edited and archived without changing stock through the edit form', function () {
    $data = Producto::factory()->raw(['stock_actual' => 5, 'precio_venta' => '12.50']);
    $this->post(route('productos.store'), $data)->assertRedirectToRoute('productos.index');
    $producto = Producto::sole();
    $this->assertDatabaseHas('producto', $data);

    $this->put(route('productos.update', $producto), [
        ...$data, 'nombre_producto' => 'Proteína', 'stock_actual' => 999,
    ])->assertRedirectToRoute('productos.index');
    expect($producto->fresh()->stock_actual)->toBe(5);
    expect($producto->fresh()->nombre_producto)->toBe('Proteína');

    $this->delete(route('productos.destroy', $producto))->assertRedirectToRoute('productos.index');
    $this->assertSoftDeleted($producto);
    expect(Producto::find($producto->getKey()))->toBeNull();
});

test('product catalog accepts descriptions and safely replaces public images', function () {
    Storage::fake('public');
    $data = Producto::factory()->raw();

    $this->post(route('productos.store'), [
        ...$data,
        'descripcion' => 'Café tostado de origen local.',
        'imagen' => UploadedFile::fake()->image('cafe.jpg'),
    ])->assertRedirectToRoute('productos.index');

    $producto = Producto::sole();
    $previousImage = $producto->imagen_path;
    expect($producto->descripcion)->toBe('Café tostado de origen local.');
    Storage::disk('public')->assertExists($previousImage);

    $this->put(route('productos.update', $producto), [
        ...$data,
        'descripcion' => 'Café molido de origen local.',
        'imagen' => UploadedFile::fake()->image('cafe-nuevo.png'),
    ])->assertRedirectToRoute('productos.index');

    expect($producto->fresh()->descripcion)->toBe('Café molido de origen local.');
    expect($producto->fresh()->imagen_path)->not->toBe($previousImage);
    Storage::disk('public')->assertMissing($previousImage);
    Storage::disk('public')->assertExists($producto->fresh()->imagen_path);
});

test('products with sales or inventory history can be archived without deleting that history', function (string $relatedClass) {
    $producto = Producto::factory()->create();
    $relatedRecord = $relatedClass::factory()->for($producto, 'producto')->create();

    $this->delete(route('productos.destroy', $producto))->assertRedirectToRoute('productos.index')
        ->assertSessionHas('mensaje', 'producto_eliminado');

    $this->assertSoftDeleted($producto);
    $this->assertModelExists($relatedRecord);
    expect($relatedRecord->fresh()->producto->is($producto))->toBeTrue();
})->with([
    'ventas' => [DetalleVenta::class],
    'inventario' => [MovimientoInventario::class],
]);

test('employees accept legacy boolean values and enforce unique identity on update', function () {
    $data = ['nombres' => 'Ana', 'documento_identidad' => '00123456', 'usuario' => 'ana', 'es_eventual' => 'true'];
    $this->post(route('empleados.store'), $data)->assertRedirectToRoute('empleados.index');
    $empleado = Empleado::sole();
    expect($empleado->es_eventual)->toBeTrue();

    $this->put(route('empleados.update', $empleado), [...$data, 'es_eventual' => 'false'])
        ->assertRedirectToRoute('empleados.index');
    expect($empleado->fresh()->es_eventual)->toBeFalse();

    $other = Empleado::factory()->create();
    $this->put(route('empleados.update', $other), $data)->assertSessionHasErrors('documento_identidad');
    expect($other->fresh()->documento_identidad)->toBe($other->documento_identidad);

    $this->delete(route('empleados.destroy', $empleado))->assertRedirectToRoute('empleados.index');
    $this->assertModelMissing($empleado);
});

test('employee identity numbers must contain exactly eight digits', function (string $document) {
    $this->post(route('empleados.store'), [
        'nombres' => 'Ana', 'documento_identidad' => $document, 'usuario' => 'ana', 'es_eventual' => 'false',
    ])->assertSessionHasErrors('documento_identidad');
    $this->assertDatabaseEmpty('empleado');
})->with(['letters' => '1234ABCD', 'too short' => '1234567', 'too long' => '123456789']);

test('employee username can be created without a seller password', function () {
    $this->post(route('empleados.store'), [
        'nombres' => 'Vendedora', 'documento_identidad' => '11111111',
        'usuario' => 'vendedora_1', 'es_eventual' => 'false',
    ])->assertRedirectToRoute('empleados.index');
    $empleado = Empleado::sole();
    expect($empleado->password)->toBeNull();

    $this->put(route('empleados.update', $empleado), [
        'nombres' => 'Vendedora', 'documento_identidad' => '11111111',
        'usuario' => 'vendedora_1', 'es_eventual' => 'false',
    ])->assertRedirectToRoute('empleados.index');
    expect($empleado->fresh()->password)->toBeNull();
});

test('a newly registered employee can sign in using the normalized username from the form', function () {
    $this->post(route('empleados.store'), [
        'nombres' => 'Brayan Huaman', 'documento_identidad' => '22222222',
        'usuario' => '  Brayan  ', 'es_eventual' => 'false',
    ])->assertRedirectToRoute('empleados.index');

    $empleado = Empleado::where('documento_identidad', '22222222')->sole();
    expect($empleado->usuario)->toBe('brayan');

    $this->post(route('logout'));
    $this->post(route('login.seller.store'), ['usuario' => ' BRAYAN '])
        ->assertRedirectToRoute('ventas.index');
    $this->assertAuthenticatedAs($empleado);
});

test('supplier CRUD and purchase prices respect both pivot keys', function () {
    $this->post(route('proveedores.store'), ['nombre_empresa' => 'Proveedor A'])
        ->assertRedirectToRoute('proveedores.index');
    $proveedor = Proveedor::sole();
    $producto = Producto::factory()->create();
    $data = ['id_producto' => $producto->getKey(), 'id_proveedor' => $proveedor->getKey(), 'precio_compra' => '8.25'];
    $this->post(route('proveedores.precios.store'), $data)->assertRedirectToRoute('proveedores.index');
    $this->assertDatabaseHas('producto_proveedor', $data);
    $this->post(route('proveedores.precios.store'), $data)->assertSessionHasErrors([
        'id_producto' => 'Este producto ya tiene un precio registrado para este proveedor.',
    ]);
    $other = ProductoProveedor::factory()->for($producto, 'producto')->create(['precio_compra' => '7.00']);

    $this->put(route('proveedores.precios.update', [$proveedor, $producto]), ['precio_compra' => '9.50'])
        ->assertRedirectToRoute('proveedores.index');
    $this->assertDatabaseHas('producto_proveedor', [...$data, 'precio_compra' => '9.50']);
    expect($other->fresh()->precio_compra)->toBe('7.00');

    $this->delete(route('proveedores.precios.destroy', [$proveedor, $producto]))
        ->assertRedirectToRoute('proveedores.index');
    $this->assertDatabaseMissing('producto_proveedor', ['id_producto' => $producto->getKey(), 'id_proveedor' => $proveedor->getKey()]);
    $this->put(route('proveedores.update', $proveedor), ['nombre_empresa' => 'Proveedor B'])
        ->assertRedirectToRoute('proveedores.index');
    expect($proveedor->fresh()->nombre_empresa)->toBe('Proveedor B');
    $this->delete(route('proveedores.destroy', $proveedor))->assertRedirectToRoute('proveedores.index');
    $this->assertModelMissing($proveedor);
});

test('supplier and sales lists expose search and date filters', function () {
    $this->get(route('proveedores.index'))->assertOk()
        ->assertSee('data-live-region="suppliers"', false)
        ->assertSee('data-live-region="supplier-prices"', false)
        ->assertSee('data-list-search-input', false);

    Venta::factory()->create(['fecha_hora' => now()]);
    $this->get(route('ventas.index'))->assertOk()
        ->assertSee('data-date-filter-start', false)
        ->assertSee('data-date-filter-end', false)
        ->assertSee('data-search-date="'.now()->format('Y-m-d').'"', false);
});

test('checkout accepts only an eight-digit DNI or eleven-digit RUC', function () {
    $producto = Producto::factory()->create(['stock_actual' => 2]);
    $this->post(route('ventas.carrito.agregar'), ['id_producto' => $producto->getKey(), 'cantidad' => 1]);

    $this->post(route('ventas.confirmar'), ['cliente_documento' => '1234ABCD', 'metodo_pago' => 'Efectivo'])
        ->assertSessionHasErrors('cliente_documento');
    $this->post(route('ventas.confirmar'), ['cliente_documento' => '12345678', 'metodo_pago' => 'Efectivo'])
        ->assertRedirectToRoute('ventas.index');

    expect(Venta::sole()->cliente_documento)->toBe('12345678');
});

test('a supplier cannot update a product price belonging to another supplier', function () {
    $precio = ProductoProveedor::factory()->create();
    $other = Proveedor::factory()->create();

    $this->put(route('proveedores.precios.update', [$other, $precio->producto]), ['precio_compra' => '1.00'])
        ->assertNotFound();
    expect($precio->fresh()->precio_compra)->toBe($precio->precio_compra);
});

test('restocking adds stock and an inventory entry together', function () {
    $producto = Producto::factory()->create(['stock_actual' => 3]);

    $this->post(route('inventario.aumentar'), ['id_producto' => $producto->getKey(), 'cantidad' => 4])
        ->assertRedirectToRoute('inventario.index');

    expect($producto->fresh()->stock_actual)->toBe(7);
    $this->assertDatabaseHas('movimiento_inventario', [
        'id_producto' => $producto->getKey(), 'cantidad' => 4,
        'tipo_movimiento' => 'ENTRADA', 'motivo' => 'Reabastecimiento',
    ]);
});

test('restocking rejects integer overflow without a movement', function () {
    $producto = Producto::factory()->create(['stock_actual' => 2147483647]);

    $this->post(route('inventario.aumentar'), ['id_producto' => $producto->getKey(), 'cantidad' => 1])
        ->assertSessionHasErrors(['cantidad' => 'El stock excede el límite permitido.']);
    expect($producto->fresh()->stock_actual)->toBe(2147483647);
    $this->assertDatabaseEmpty('movimiento_inventario');
});

test('cart operations accumulate quantities and reject stock overflow', function () {
    $producto = Producto::factory()->create(['stock_actual' => 5, 'precio_venta' => '1.25']);
    $data = ['id_producto' => $producto->getKey(), 'cantidad' => 2];
    $this->post(route('ventas.carrito.agregar'), [...$data, 'precio_unitario' => 0])->assertRedirectToRoute('ventas.index');
    $this->post(route('ventas.carrito.agregar'), $data)->assertSessionHas('carrito.'.$producto->getKey().'.cantidad', 4);
    $this->post(route('ventas.carrito.agregar'), $data)->assertSessionHasErrors([
        'cantidad' => 'La cantidad solicitada supera el stock disponible.',
    ]);
    expect(session('carrito.'.$producto->getKey().'.precio_unitario'))->toBe('1.25');

    $this->delete(route('ventas.carrito.eliminar', $producto))->assertRedirectToRoute('ventas.index')
        ->assertSessionMissing('carrito.'.$producto->getKey());
    $this->post(route('ventas.carrito.agregar'), $data);
    $this->delete(route('ventas.carrito.vaciar'))->assertSessionHas('carrito', []);
    expect($producto->fresh()->stock_actual)->toBe(5);
});

test('checkout uses current product prices records details and stock exits then clears the cart', function () {
    $producto = Producto::factory()->create(['stock_actual' => 5, 'precio_venta' => '0.10']);
    $this->post(route('ventas.carrito.agregar'), ['id_producto' => $producto->getKey(), 'cantidad' => 3]);
    $producto->update(['precio_venta' => '0.20']);

    $this->post(route('ventas.confirmar'), ['metodo_pago' => 'Yape', 'total_venta' => 1])
        ->assertRedirectToRoute('ventas.index')->assertSessionHas('carrito', []);

    $venta = Venta::sole();
    expect($venta->total_venta)->toBe('0.60');
    expect($producto->fresh()->stock_actual)->toBe(2);
    $this->assertDatabaseHas('detalle_venta', [
        'id_venta' => $venta->getKey(), 'id_producto' => $producto->getKey(), 'cantidad' => 3, 'precio_unitario' => '0.20',
    ]);
    $this->assertDatabaseHas('movimiento_inventario', [
        'id_producto' => $producto->getKey(), 'cantidad' => 3, 'tipo_movimiento' => 'SALIDA', 'motivo' => 'Venta #'.$venta->getKey(),
    ]);
    expect(session('venta_exitosa'))->toBe($venta->getKey());

    $this->post(route('ventas.confirmar'), ['metodo_pago' => 'Yape'])
        ->assertSessionHasErrors(['carrito' => 'El carrito está vacío.']);
    $this->assertDatabaseCount('venta', 1);
});

test('JSON point of sale checkout uses database prices and updates inventory without a session cart', function () {
    $producto = Producto::factory()->create(['stock_actual' => 4, 'precio_venta' => '2.75']);
    $this->actingAs(Empleado::factory()->withAccess('empleado')->make(['id_empleado' => 43]));

    $response = $this->postJson(route('ventas.confirmar'), [
        'cliente_nombre' => 'Cliente POS',
        'metodo_pago' => 'Efectivo',
        'items' => [['id_producto' => $producto->getKey(), 'cantidad' => 2, 'precio' => 0.01]],
    ])->assertCreated()->assertJsonPath('total_venta', '5.50');

    $venta = Venta::sole();
    expect($response->json('venta_id'))->toBe($venta->getKey());
    expect($response->json('recibo_url'))->toBe(route('ventas.pdf', $venta));
    expect($producto->fresh()->stock_actual)->toBe(2);
    $this->assertDatabaseHas('detalle_venta', [
        'id_venta' => $venta->getKey(),
        'id_producto' => $producto->getKey(),
        'cantidad' => 2,
        'precio_unitario' => '2.75',
    ]);
    $this->assertDatabaseHas('movimiento_inventario', [
        'id_producto' => $producto->getKey(),
        'cantidad' => 2,
        'tipo_movimiento' => 'SALIDA',
    ]);
    $response->assertSessionMissing('carrito');
});

test('JSON point of sale checkout rejects insufficient stock without saving a sale', function () {
    $producto = Producto::factory()->create(['stock_actual' => 1]);

    $this->postJson(route('ventas.confirmar'), [
        'metodo_pago' => 'Yape',
        'items' => [['id_producto' => $producto->getKey(), 'cantidad' => 2]],
    ])->assertUnprocessable()->assertJsonValidationErrors('carrito');

    expect($producto->fresh()->stock_actual)->toBe(1);
    $this->assertDatabaseEmpty('venta');
    $this->assertDatabaseEmpty('detalle_venta');
    $this->assertDatabaseEmpty('movimiento_inventario');
});

test('checkout rolls back all lines when a later product is out of stock or removed', function (bool $removed) {
    $first = Producto::factory()->create(['stock_actual' => 5]);
    $second = Producto::factory()->create(['stock_actual' => 5]);
    foreach ([$first, $second] as $producto) {
        $this->post(route('ventas.carrito.agregar'), ['id_producto' => $producto->getKey(), 'cantidad' => 2]);
    }
    if ($removed) {
        $second->delete();
    } else {
        $second->update(['stock_actual' => 1]);
    }

    $this->post(route('ventas.confirmar'), ['metodo_pago' => 'Efectivo'])
        ->assertSessionHasErrors(['carrito' => 'Un producto ya no está disponible o no tiene stock suficiente.'])
        ->assertSessionHas('carrito.'.$first->getKey().'.cantidad', 2);

    expect($first->fresh()->stock_actual)->toBe(5);
    $this->assertDatabaseEmpty('venta');
    $this->assertDatabaseEmpty('detalle_venta');
    $this->assertDatabaseEmpty('movimiento_inventario');
})->with([false, true]);

test('checkout rejects a total beyond the decimal column capacity', function () {
    $producto = Producto::factory()->create(['stock_actual' => 2, 'precio_venta' => '99999999.99']);
    $this->post(route('ventas.carrito.agregar'), ['id_producto' => $producto->getKey(), 'cantidad' => 2]);

    $this->post(route('ventas.confirmar'), ['metodo_pago' => 'Tarjeta'])->assertSessionHasErrors('carrito');

    expect($producto->fresh()->stock_actual)->toBe(2);
    $this->assertDatabaseEmpty('venta');
    $this->assertDatabaseEmpty('detalle_venta');
});

test('checkout rejects negative prices inherited from legacy data', function () {
    $producto = Producto::factory()->create(['stock_actual' => 2, 'precio_venta' => '-0.50']);
    $this->post(route('ventas.carrito.agregar'), ['id_producto' => $producto->getKey(), 'cantidad' => 1]);

    $this->post(route('ventas.confirmar'), ['metodo_pago' => 'Efectivo'])->assertSessionHasErrors('carrito');

    expect($producto->fresh()->stock_actual)->toBe(2);
    $this->assertDatabaseEmpty('venta');
    $this->assertDatabaseEmpty('detalle_venta');
    $this->assertDatabaseEmpty('movimiento_inventario');
});

test('stock operations reject non-positive and fractional quantities', function (string $routeName, mixed $quantity) {
    $producto = Producto::factory()->create(['stock_actual' => 5]);

    $this->post(route($routeName), ['id_producto' => $producto->getKey(), 'cantidad' => $quantity])
        ->assertSessionHasErrors('cantidad');

    expect($producto->fresh()->stock_actual)->toBe(5);
    $this->assertDatabaseEmpty('movimiento_inventario');
})->with(['inventario.aumentar', 'ventas.carrito.agregar'])->with([0, -1, 1.5]);

test('manual attendance rejects duplicate dates and invalid time order', function () {
    $empleado = Empleado::factory()->create();
    $data = ['id_empleado' => $empleado->getKey(), 'fecha' => '2026-09-25', 'hora_ingreso' => '08:00', 'hora_salida' => '17:00'];
    $this->post(route('asistencia.store'), $data)->assertRedirectToRoute('asistencia.index');
    $asistencia = Asistencia::sole();
    $this->post(route('asistencia.store'), $data)->assertSessionHasErrors([
        'fecha' => 'El empleado ya tiene asistencia registrada en esta fecha.',
    ]);
    $this->put(route('asistencia.update', $asistencia), [...$data, 'hora_salida' => '07:00'])
        ->assertSessionHasErrors('hora_salida');
    $this->put(route('asistencia.update', $asistencia), [...$data, 'hora_salida' => '18:00'])
        ->assertRedirectToRoute('asistencia.index');
    expect($asistencia->fresh()->hora_salida)->toBe('18:00');

    $this->delete(route('asistencia.destroy', $asistencia))->assertRedirectToRoute('asistencia.index');
    $this->assertModelMissing($asistencia);
});

test('manual attendance registration does not require entering check-in or check-out times', function () {
    $empleado = Empleado::factory()->create();

    $this->get(route('asistencia.index'))->assertDontSee('name="hora_ingreso"', false)
        ->assertDontSee('name="hora_salida"', false);
    $this->post(route('asistencia.store'), [
        'id_empleado' => $empleado->getKey(),
        'fecha' => '2026-09-25',
    ])->assertRedirectToRoute('asistencia.index');

    $asistencia = Asistencia::sole();
    expect($asistencia->hora_ingreso)->toBeNull();
    expect($asistencia->hora_salida)->toBeNull();
});

test('attendance page removes manual registration and provides clock controls for regular and temporary employees', function () {
    $empleado = Empleado::factory()->create();
    EmpleadoTemporal::create([
        'nombre_temp' => 'Ana Temporal',
    ]);

    $this->get(route('asistencia.index'))->assertOk()
        ->assertDontSee('Registrar asistencia</h2>')
        ->assertDontSee('temporal-fecha-inicio')
        ->assertDontSee('temporal-fecha-fin')
        ->assertSee('Registrar ingreso')
        ->assertSee('Registrar salida')
        ->assertSee('Empleado temporal')
        ->assertSee('Ana Temporal')
        ->assertSee((string) $empleado->nombres);
});

test('attendance inventory shows daily attendance for employees and temporary employees', function () {
    $this->travelTo(now()->setDate(2026, 10, 9)->setTime(12, 0));
    $asistio = Empleado::factory()->create(['nombres' => 'Empleado Presente']);
    Empleado::factory()->create(['nombres' => 'Empleado Ausente']);
    Empleado::factory()->create(['nombres' => 'Empleado de Otro Dia']);

    Asistencia::create([
        'id_empleado' => $asistio->getKey(),
        'fecha' => '2026-10-09',
        'hora_ingreso' => '08:05:00',
        'hora_salida' => '11:45:00',
    ]);
    Asistencia::create([
        'id_empleado' => Empleado::where('nombres', 'Empleado de Otro Dia')->value('id_empleado'),
        'fecha' => '2026-10-08',
        'hora_ingreso' => '08:00:00',
        'hora_salida' => '17:00:00',
    ]);
    EmpleadoTemporal::create([
        'nombre_temp' => 'Temporal Presente',
        'fecha_inicio' => '2026-10-09',
        'hora_ingreso' => '09:00:00',
        'hora_salida' => '12:30:00',
    ]);
    EmpleadoTemporal::create([
        'nombre_temp' => 'Temporal de Otro Dia',
        'fecha_inicio' => '2026-10-08',
        'hora_ingreso' => '08:00:00',
        'hora_salida' => '17:00:00',
    ]);

    $this->get(route('asistencia.inventario'))
        ->assertOk()
        ->assertViewIs('asistencia.inventario')
        ->assertSee('Empleado Presente')
        ->assertSee('Empleado Ausente')
        ->assertSee('Asistió')
        ->assertSee('No asistió')
        ->assertSee('08:05:00')
        ->assertSee('11:45:00')
        ->assertSee('Temporal Presente')
        ->assertSee('09:00:00')
        ->assertSee('12:30:00')
        ->assertSee('Empleado de Otro Dia')
        ->assertDontSee('Temporal de Otro Dia')
        ->assertSee('value="2026-10-09"', false);

    $this->get(route('asistencia.inventario', ['fecha' => '2026-10-08']))
        ->assertOk()
        ->assertSee('Empleado de Otro Dia')
        ->assertSee('Temporal de Otro Dia')
        ->assertSee('08:00:00')
        ->assertDontSee('Temporal Presente')
        ->assertSee('value="2026-10-08"', false);
});

test('temporary employees can be registered by name only and appear in their searchable history', function () {
    $this->post(route('asistencia.temporales.store'), [
        'nombre_temp' => 'Ana Temporal',
    ])->assertRedirectToRoute('asistencia.index')->assertSessionHas('mensaje', 'temporal_registrado');

    $periodo = EmpleadoTemporal::sole();
    expect($periodo->nombre_temp)->toBe('Ana Temporal');
    expect($periodo->id_empleado)->toBeNull();
    expect($periodo->fecha_inicio)->toBeNull();
    expect($periodo->fecha_fin)->toBeNull();

    $this->get(route('asistencia.index'))->assertOk()
        ->assertSee('Historial de empleados temporales')
        ->assertSee('Ana Temporal')
        ->assertSee('Hora de ingreso')
        ->assertSee('Hora de salida')
        ->assertSee('data-live-region="temporary-employees"', false)
        ->assertSee('data-list-search-input', false);
});

test('temporary employee periods require a name of at most one hundred characters', function (string $name) {
    $this->post(route('asistencia.temporales.store'), [
        'nombre_temp' => $name,
    ])->assertSessionHasErrors('nombre_temp');

    $this->assertDatabaseEmpty('empleado_temporal');
})->with([
    'missing name' => [''],
    'name too long' => [str_repeat('a', 101)],
]);

test('temporary employees can clock in once and clock out only after clocking in', function () {
    $this->travelTo(now()->setTime(8, 0));
    $temporal = EmpleadoTemporal::create([
        'nombre_temp' => 'Ana Temporal',
    ]);
    $data = ['id_temporal' => $temporal->getKey()];

    $this->post(route('asistencia.temporales.salida'), $data)->assertSessionHasErrors([
        'id_temporal' => 'Este empleado temporal no tiene un ingreso abierto para registrar la salida.',
    ]);
    $this->post(route('asistencia.temporales.ingreso'), $data)->assertRedirectToRoute('asistencia.index');
    $this->post(route('asistencia.temporales.ingreso'), $data)->assertSessionHasErrors([
        'id_temporal' => 'Este empleado temporal ya tiene un ingreso registrado.',
    ]);

    $this->travel(3)->hours();
    $this->post(route('asistencia.temporales.salida'), $data)->assertRedirectToRoute('asistencia.index')
        ->assertSessionHas('mensaje', 'temporal_salida');
    $this->assertDatabaseHas('empleado_temporal', [
        'id_temporal' => $temporal->getKey(),
        'fecha_inicio' => now()->toDateString(),
        'hora_ingreso' => '08:00:00',
        'hora_salida' => '11:00:00',
    ]);
});

test('clock in and out require an open attendance and allow a later shift', function () {
    $this->travelTo(now()->setDate(2026, 9, 25)->setTime(8, 0));
    $empleado = Empleado::factory()->create();
    $data = ['id_empleado' => $empleado->getKey()];
    $this->post(route('asistencia.salida'), $data)->assertSessionHasErrors([
        'id_empleado' => 'Este empleado no tiene un ingreso abierto para registrar la salida.',
    ]);
    $this->post(route('asistencia.ingreso'), $data)->assertRedirectToRoute('asistencia.index');
    $this->post(route('asistencia.ingreso'), $data)->assertSessionHasErrors([
        'id_empleado' => 'Este empleado ya tiene un ingreso abierto.',
    ]);
    $this->travel(4)->hours();
    $this->post(route('asistencia.salida'), $data)->assertRedirectToRoute('asistencia.index');
    $this->assertDatabaseHas('asistencia', [
        'id_empleado' => $empleado->getKey(),
        'hora_ingreso' => '08:00:00', 'hora_salida' => '12:00:00',
    ]);
    expect(Asistencia::sole()->fecha->toDateString())->toBe('2026-09-25');
    $this->post(route('asistencia.ingreso'), $data)->assertRedirectToRoute('asistencia.index');
    $this->assertDatabaseCount('asistencia', 2);
});

test('dashboard totals distinguish current previous and future months and per-product stock limits', function () {
    $this->travelTo(now()->setDate(2026, 9, 25));
    $producto = Producto::factory()->create(['stock_actual' => 12, 'stock_minimo_alerta' => 15]);
    Producto::factory()->create(['stock_actual' => 6, 'stock_minimo_alerta' => 5]);
    $actual = Venta::factory()->create(['fecha_hora' => '2026-09-01 00:00:00', 'total_venta' => '30.00', 'metodo_pago' => 'Yape']);
    DetalleVenta::factory()->for($actual, 'venta')->for($producto, 'producto')->create(['cantidad' => 3]);
    Venta::factory()->create(['fecha_hora' => '2026-08-31 23:59:59', 'total_venta' => '20.00']);
    Venta::factory()->create(['fecha_hora' => '2026-10-01 00:00:00', 'total_venta' => '500.00']);

    $response = $this->getJson(route('dashboard.data'))->assertOk();

    expect($response->json('resumen'))->toEqual(['cantidad_ventas' => 1, 'ingresos' => 30, 'productos_vendidos' => 3]);
    expect($response->json('variacion.porcentaje'))->toEqual(50);
    expect($response->json('ranking.0.id'))->toBe($producto->getKey());
    expect($response->json('cantidad_stock_bajo'))->toBe(1);
    expect($response->json('metodos.0.porcentaje'))->toEqual(100);
    expect(collect($response->json('meses'))->pluck('mes')->all())->toHaveCount(12);
    expect(collect($response->json('meses'))->pluck('mes')->last())->toBe('2026-09');
});

test('dashboard has no HTML tables and limits the product chart to five items', function () {
    foreach (range(1, 6) as $quantity) {
        $producto = Producto::factory()->create();
        $venta = Venta::factory()->create();
        DetalleVenta::factory()->for($venta, 'venta')->for($producto, 'producto')->create(['cantidad' => $quantity]);
    }

    $this->get(route('dashboard.index'))->assertOk()
        ->assertDontSee('<table', false)
        ->assertSee('Resumen General')
        ->assertDontSee('GESTIÓN EMPRESARIAL')
        ->assertSee('Evolución de Ingresos')
        ->assertSee('Métodos de Pago')
        ->assertSee('Top 5 Productos Más Vendidos')
        ->assertSee('dashboard-skeleton', false)
        ->assertSee('data-dashboard-endpoint', false)
        ->assertDontSee('data-live-lists', false);

    $this->getJson(route('dashboard.data'))->assertOk()->assertJsonCount(5, 'ranking');
});

test('attendance page displays a fallback for a record without a related employee', function () {
    $asistencia = Asistencia::factory()->make([
        'id_asistencia' => 999,
        'id_empleado' => 999,
        'fecha' => '2026-09-28',
    ]);

    view()->share('errors', new ViewErrorBag);
    $html = view('asistencia.index', [
        'empleados' => collect(),
        'asistencias' => collect([$asistencia]),
        'empleadosTemporales' => collect(),
    ])->render();

    expect($html)->toContain('Empleado eliminado')->toContain('—');
});

test('live pages expose automatic refresh regions and searchable suggestions', function (string $routeName, string $region, bool $searchable) {
    $response = $this->get(route($routeName))->assertOk()->assertSee('data-live-lists', false)
        ->assertSee('data-live-region="'.$region.'"', false);

    if ($searchable) {
        $response->assertSee('data-list-search-input', false)->assertSee('role="listbox"', false);
    }

})->with([
    ['productos.index', 'products', true],
    ['inventario.index', 'inventory-products', true],
    ['inventario.index', 'inventory-movements', true],
    ['ventas.index', 'sales', true],
    ['empleados.index', 'employees', true],
    ['asistencia.index', 'attendance', true],
]);

test('PDF download renders actual sale details and escapes free text in its template', function () {
    $venta = Venta::factory()->create(['cliente_nombre' => '<script>alert(1)</script>', 'total_venta' => '25.00']);
    $detalle = DetalleVenta::factory()->for($venta, 'venta')->create(['cantidad' => 2, 'precio_unitario' => '12.50']);
    $venta->load('detalles.producto');

    $html = view('ventas.factura', ['venta' => $venta, 'detalles' => $venta->detalles])->render();
    expect($html)->toContain('&lt;script&gt;')->not->toContain('<script>alert(1)</script>');
    expect($html)->toContain(e($detalle->producto->nombre_producto), '25.00');
    $response = $this->get(route('ventas.pdf', $venta))->assertOk()->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('Recibo_Venta_'.$venta->getKey().'.pdf');
    expect($response->getContent())->toStartWith('%PDF-');
});

test('missing legacy records return 404', function (string $routeName) {
    $this->get(route($routeName, 999999))->assertNotFound();
})->with(['productos.edit', 'empleados.edit', 'asistencia.edit', 'ventas.show', 'ventas.pdf']);

test('write endpoints reject missing required input without creating records', function (string $routeName, array $errors, string $table) {
    $this->post(route($routeName), [])->assertSessionHasErrors($errors);
    $this->assertDatabaseEmpty($table);
})->with([
    ['productos.store', ['nombre_producto', 'stock_actual', 'stock_minimo_alerta', 'precio_venta'], 'producto'],
    ['empleados.store', ['nombres', 'documento_identidad', 'usuario'], 'empleado'],
    ['proveedores.store', ['nombre_empresa'], 'proveedor'],
    ['proveedores.precios.store', ['id_producto', 'id_proveedor', 'precio_compra'], 'producto_proveedor'],
    ['inventario.aumentar', ['id_producto', 'cantidad'], 'movimiento_inventario'],
    ['asistencia.store', ['id_empleado', 'fecha'], 'asistencia'],
    ['asistencia.ingreso', ['id_empleado'], 'asistencia'],
    ['asistencia.salida', ['id_empleado'], 'asistencia'],
    ['ventas.carrito.agregar', ['id_producto', 'cantidad'], 'venta'],
    ['ventas.confirmar', ['metodo_pago'], 'venta'],
]);

test('product inputs reject invalid amounts and lengths', function (string $field, mixed $value) {
    $data = Producto::factory()->raw();

    $this->post(route('productos.store'), [...$data, $field => $value])->assertSessionHasErrors($field);
    $this->assertDatabaseEmpty('producto');
})->with([
    ['nombre_producto', str_repeat('a', 151)],
    ['stock_actual', -1], ['stock_actual', 1.5], ['stock_minimo_alerta', -1],
    ['precio_venta', -1], ['precio_venta', '1.234'], ['precio_venta', '100000000.00'],
]);

test('GET cannot execute mutations', function (string $routeName) {
    $this->get(route($routeName))->assertMethodNotAllowed();
})->with(['inventario.aumentar', 'asistencia.ingreso', 'asistencia.salida', 'ventas.carrito.vaciar']);

test('checkout rejects unsupported payment methods', function () {
    $this->post(route('ventas.confirmar'), ['metodo_pago' => 'Bitcoin'])->assertSessionHasErrors('metodo_pago');
    $this->assertDatabaseEmpty('venta');
});

test('inventory write failures roll back stock and sale records while preserving the cart', function (bool $checkout) {
    $producto = Producto::factory()->create(['stock_actual' => 5]);
    if ($checkout) {
        $this->post(route('ventas.carrito.agregar'), ['id_producto' => $producto->getKey(), 'cantidad' => 2]);
    }
    $dispatcher = MovimientoInventario::getEventDispatcher();
    MovimientoInventario::setEventDispatcher(clone $dispatcher);
    MovimientoInventario::creating(function (): void {
        throw new RuntimeException('Inventory write failed');
    });
    $this->withoutExceptionHandling();

    try {
        expect(fn () => $this->post(
            route($checkout ? 'ventas.confirmar' : 'inventario.aumentar'),
            $checkout ? ['metodo_pago' => 'Efectivo'] : ['id_producto' => $producto->getKey(), 'cantidad' => 2],
        ))->toThrow(RuntimeException::class, 'Inventory write failed');

        expect($producto->fresh()->stock_actual)->toBe(5);
        $this->assertDatabaseEmpty('venta');
        $this->assertDatabaseEmpty('detalle_venta');
        $this->assertDatabaseEmpty('movimiento_inventario');
        if ($checkout) {
            expect(session('carrito.'.$producto->getKey().'.cantidad'))->toBe(2);
        }
    } finally {
        MovimientoInventario::setEventDispatcher($dispatcher);
    }
})->with(['restocking' => false, 'checkout' => true]);

test('populated module views render relations escape names and include protected forms', function () {
    $nombre = '<b>Proteína & energía</b>';
    $producto = Producto::factory()->create(['nombre_producto' => $nombre, 'stock_actual' => 5]);
    ProductoProveedor::factory()->for($producto, 'producto')->create();
    MovimientoInventario::factory()->for($producto, 'producto')->create();
    $empleado = Empleado::factory()->create(['nombres' => '<b>Ana & José</b>']);
    Asistencia::factory()->for($empleado, 'empleado')->create();
    $detalle = DetalleVenta::factory()->for($producto, 'producto')->create(['cantidad' => 2, 'precio_unitario' => '12.50']);

    foreach (['productos.index', 'proveedores.index', 'inventario.index'] as $routeName) {
        $this->get(route($routeName))->assertOk()->assertSee($nombre)->assertDontSee($nombre, false)
            ->assertSee('name="_token"', false);
    }
    foreach (['empleados.index', 'asistencia.index'] as $routeName) {
        $this->get(route($routeName))->assertOk()->assertSee($empleado->nombres)
            ->assertDontSee($empleado->nombres, false)->assertSee('value="DELETE"', false);
    }
    $this->get(route('productos.edit', $producto))->assertOk()->assertSee('value="PUT"', false);
    $this->get(route('ventas.show', $detalle->venta))->assertOk()->assertSee($nombre)->assertSee('25.00')
        ->assertSee(route('ventas.pdf', $detalle->venta));
    $this->post(route('ventas.carrito.agregar'), ['id_producto' => $producto->getKey(), 'cantidad' => 1]);
    foreach (['ventas.index', 'ventas.nueva'] as $routeName) {
        $this->get(route($routeName))->assertOk()->assertSee($nombre)->assertSee('Confirmar venta')
            ->assertSee('value="DELETE"', false)->assertSee('name="_token"', false);
    }
});

test('long receipts render all sale lines across multiple pages', function () {
    $venta = Venta::factory()->create(['cliente_nombre' => 'José Muñoz', 'metodo_pago' => 'Efectivo', 'total_venta' => '1125.00']);
    $producto = Producto::factory()->create(['nombre_producto' => str_repeat('Proteína y energía ', 7)]);
    DetalleVenta::factory()->count(45)->for($venta, 'venta')->for($producto, 'producto')
        ->create(['cantidad' => 2, 'precio_unitario' => '12.50']);

    $response = $this->get(route('ventas.pdf', $venta))->assertOk()->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('Recibo_Venta_'.$venta->getKey().'.pdf');
    expect($response->getContent())->toStartWith('%PDF-');
    $venta->load('detalles.producto');
    $pdf = Pdf::loadView('ventas.factura', ['venta' => $venta, 'detalles' => $venta->detalles]);
    $pdf->render();
    expect($pdf->getDomPDF()->getCanvas()->get_page_count())->toBeGreaterThan(1);
});
