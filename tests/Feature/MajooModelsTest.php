<?php

use App\Models\Asistencia;
use App\Models\DetalleVenta;
use App\Models\Empleado;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoProveedor;
use App\Models\Proveedor;
use App\Models\Venta;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');

    Schema::create('empleado', function (Blueprint $table) {
        $table->increments('id_empleado');
        $table->string('nombres', 100);
        $table->string('documento_identidad', 20)->unique();
        $table->boolean('es_eventual')->nullable()->default(false);
    });

    Schema::create('asistencia', function (Blueprint $table) {
        $table->increments('id_asistencia');
        $table->integer('id_empleado');
        $table->date('fecha');
        $table->time('hora_ingreso')->nullable();
        $table->time('hora_salida')->nullable();
        $table->foreign('id_empleado')->references('id_empleado')->on('empleado');
    });

    Schema::create('producto', function (Blueprint $table) {
        $table->increments('id_producto');
        $table->string('nombre_producto', 150);
        $table->integer('stock_actual')->default(0);
        $table->integer('stock_minimo_alerta')->default(10);
        $table->decimal('precio_venta', 10, 2);
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
        $table->foreign('id_producto')->references('id_producto')->on('producto');
        $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedor');
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

test('models persist and update using the legacy keys without timestamp columns', function (string $modelClass, string $field, mixed $value) {
    $attributes = $modelClass::factory()->raw();
    $model = $modelClass::query()->create($attributes);
    $loaded = $modelClass::query()->findOrFail($model->getKey());

    $loaded->update([$field => $value]);

    expect($loaded->fresh()->getAttribute($field))->toBe($value);
    $this->assertModelExists($loaded);
})->with([
    'asistencia' => [Asistencia::class, 'hora_salida', '17:30:00'],
    'empleado' => [Empleado::class, 'nombres', 'Empleado actualizado'],
    'movimiento' => [MovimientoInventario::class, 'motivo', 'Ajuste de inventario'],
    'producto' => [Producto::class, 'nombre_producto', 'Producto actualizado'],
    'proveedor' => [Proveedor::class, 'nombre_empresa', 'Proveedor actualizado'],
    'venta' => [Venta::class, 'cliente_nombre', 'Cliente actualizado'],
    'detalle' => [DetalleVenta::class, 'cantidad', 3],
]);

test('attendance belongs to its employee and preserves dates and nullable times', function () {
    $empleado = Empleado::factory()->create(['es_eventual' => true]);
    $asistencia = Asistencia::factory()->for($empleado, 'empleado')->create(['fecha' => '2026-09-25']);
    Asistencia::factory()->create();

    $loaded = Empleado::with('asistencias.empleado')->findOrFail($empleado->getKey());

    expect($loaded->es_eventual)->toBeTrue();
    expect($loaded->asistencias->modelKeys())->toBe([$asistencia->getKey()]);
    expect($loaded->asistencias->first()->empleado->is($empleado))->toBeTrue();
    expect($loaded->asistencias->first()->fecha->format('Y-m-d'))->toBe('2026-09-25');
    expect($loaded->asistencias->first()->hora_salida)->toBeNull();
});

test('products load only their inventory movements and sales with line prices', function () {
    $producto = Producto::factory()->create(['precio_venta' => '12.50']);
    $venta = Venta::factory()->create(['total_venta' => '25.00']);
    $detalle = DetalleVenta::factory()->for($producto, 'producto')->for($venta, 'venta')
        ->create(['cantidad' => 2, 'precio_unitario' => '12.50']);
    $movimiento = MovimientoInventario::factory()->for($producto, 'producto')
        ->create(['fecha_hora' => '2026-09-25 09:15:00']);
    DetalleVenta::factory()->create();
    MovimientoInventario::factory()->create();

    $loaded = $producto->fresh(['movimientosInventario.producto', 'detallesVenta.venta', 'ventas']);
    $loadedVenta = $venta->fresh(['detalles.producto', 'productos']);

    expect($loaded->precio_venta)->toBe('12.50');
    expect($loaded->movimientosInventario->modelKeys())->toBe([$movimiento->getKey()]);
    expect($loaded->movimientosInventario->first()->producto->is($producto))->toBeTrue();
    expect($loaded->movimientosInventario->first()->fecha_hora->format('Y-m-d H:i:s'))->toBe('2026-09-25 09:15:00');
    expect($loaded->detallesVenta->modelKeys())->toBe([$detalle->getKey()]);
    expect($loaded->detallesVenta->first()->venta->is($venta))->toBeTrue();
    expect($loaded->ventas->modelKeys())->toBe([$venta->getKey()]);
    expect($loadedVenta->total_venta)->toBe('25.00');
    expect($loadedVenta->detalles->modelKeys())->toBe([$detalle->getKey()]);
    expect($loadedVenta->detalles->first()->producto->is($producto))->toBeTrue();
    expect($loadedVenta->detalles->first()->precio_unitario)->toBe('12.50');
    expect($loadedVenta->productos->modelKeys())->toBe([$producto->getKey()]);
    expect($loadedVenta->productos->first()->pivot->cantidad)->toBe(2);
});

test('supplier relationships expose the custom pivot and purchase price in both directions', function () {
    $producto = Producto::factory()->create();
    $proveedor = Proveedor::factory()->create();
    $producto->proveedores()->attach($proveedor, ['precio_compra' => '7.50']);
    ProductoProveedor::factory()->create();

    $loaded = $producto->fresh(['proveedores', 'productosProveedores.proveedor']);
    $loadedProveedor = $proveedor->fresh(['productos', 'productosProveedores.producto']);

    expect($loaded->proveedores->modelKeys())->toBe([$proveedor->getKey()]);
    expect($loaded->proveedores->first()->pivot)->toBeInstanceOf(ProductoProveedor::class);
    expect($loaded->proveedores->first()->pivot->precio_compra)->toBe('7.50');
    expect($loaded->productosProveedores)->toHaveCount(1);
    expect($loaded->productosProveedores->first()->proveedor->is($proveedor))->toBeTrue();
    expect($loadedProveedor->productos->modelKeys())->toBe([$producto->getKey()]);
    expect($loadedProveedor->productos->first()->pivot->precio_compra)->toBe('7.50');
    expect($loadedProveedor->productosProveedores)->toHaveCount(1);
    expect($loadedProveedor->productosProveedores->first()->producto->is($producto))->toBeTrue();
});

test('updating and deleting a purchase price preserves other pairs sharing either key', function (string $source) {
    $producto = Producto::factory()->create();
    $proveedor = Proveedor::factory()->create();
    $precio = ProductoProveedor::query()->create([
        'id_producto' => $producto->getKey(),
        'id_proveedor' => $proveedor->getKey(),
        'precio_compra' => '5.00',
    ]);
    $otherSupplier = ProductoProveedor::factory()->for($producto, 'producto')->create(['precio_compra' => '8.00']);
    $otherProduct = ProductoProveedor::factory()->for($proveedor, 'proveedor')->create(['precio_compra' => '9.00']);
    $loaded = match ($source) {
        'direct' => ProductoProveedor::where('id_producto', $producto->getKey())->where('id_proveedor', $proveedor->getKey())->firstOrFail(),
        'product' => $producto->proveedores()->findOrFail($proveedor->getKey())->pivot,
        'supplier' => $proveedor->productos()->findOrFail($producto->getKey())->pivot,
    };

    $loaded->update(['precio_compra' => '6.25']);

    expect($precio->fresh()->precio_compra)->toBe('6.25');
    $loaded->delete();
    $this->assertDatabaseMissing('producto_proveedor', [
        'id_producto' => $producto->getKey(),
        'id_proveedor' => $proveedor->getKey(),
    ]);
    $this->assertDatabaseHas('producto_proveedor', $otherSupplier->getAttributes());
    $this->assertDatabaseHas('producto_proveedor', $otherProduct->getAttributes());
})->with(['direct', 'product', 'supplier']);

afterEach(function () {
    DB::purge('sqlite');
});
