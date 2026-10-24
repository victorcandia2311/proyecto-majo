<?php

use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PasswordRecoveryController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'home'])->name('inicio');
Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login/admin', [AuthController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('login/administrador', [AuthController::class, 'adminLogin'])->name('login.admin');
    Route::get('login/vendedor', [AuthController::class, 'sellerLogin'])->name('login.seller');
    Route::post('login/vendedor', [AuthController::class, 'storeSeller'])->middleware('throttle:login')->name('login.seller.store');
    Route::get('registro/administrador', [AuthController::class, 'createAdminRegistration'])->name('admin.register');
    Route::post('registro/administrador/codigo', [AuthController::class, 'sendAdminRegistrationCode'])->middleware('throttle:recovery-send')->name('admin.register.code');
    Route::post('registro/administrador/codigo/verificar', [AuthController::class, 'verifyAdminRegistrationCode'])->middleware('throttle:recovery-reset')->name('admin.register.code.verify');
    Route::post('registro/administrador', [AuthController::class, 'registerAdmin'])->middleware('throttle:recovery-reset')->name('admin.register.store');
    Route::get('olvide-mi-contrasena', [PasswordRecoveryController::class, 'create'])->name('password.request');
    Route::post('olvide-mi-contrasena', [PasswordRecoveryController::class, 'store'])->middleware('throttle:recovery-send')->name('password.email');
    Route::get('restablecer-contrasena', [PasswordRecoveryController::class, 'edit'])->name('password.reset');
    Route::post('restablecer-contrasena', [PasswordRecoveryController::class, 'update'])->middleware('throttle:recovery-reset')->name('password.update');
});
Route::post('logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'auth.session', 'role:admin,empleado'])->group(function (): void {
    Route::middleware('role:admin')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

        Route::resource('productos', ProductoController::class)->except('show')
            ->parameters(['productos' => 'producto']);
        Route::resource('empleados', EmpleadoController::class)->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['empleados' => 'empleado']);

        Route::prefix('proveedores')->name('proveedores.')->controller(ProveedorController::class)->group(function (): void {
            Route::get('asignar-producto', 'asignarProducto')->name('asignar-producto');
            Route::post('precios', 'guardarPrecio')->name('precios.store');
            Route::put('{proveedor}/productos/{producto}', 'actualizarPrecio')->name('precios.update');
            Route::delete('{proveedor}/productos/{producto}', 'eliminarPrecio')->name('precios.destroy');
        });
        Route::resource('proveedores', ProveedorController::class)->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['proveedores' => 'proveedor']);

        Route::prefix('inventario')->name('inventario.')->controller(InventarioController::class)->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::post('aumentar', 'aumentar')->name('aumentar');
        });

        Route::prefix('asistencia')->name('asistencia.')->controller(AsistenciaController::class)->group(function (): void {
            Route::get('inventario', 'inventario')->name('inventario');
            Route::post('ingreso', 'registrarIngreso')->name('ingreso');
            Route::post('salida', 'registrarSalida')->name('salida');
            Route::post('temporal', 'registrarTemporal')->name('temporales.store');
            Route::post('temporal/ingreso', 'registrarIngresoTemporal')->name('temporales.ingreso');
            Route::post('temporal/salida', 'registrarSalidaTemporal')->name('temporales.salida');
        });
        Route::resource('asistencia', AsistenciaController::class)->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['asistencia' => 'asistencia']);

    });

    Route::prefix('ventas')->name('ventas.')->controller(VentaController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('nueva', 'create')->name('nueva');
        Route::post('carrito', 'agregar')->name('carrito.agregar')->block();
        Route::delete('carrito', 'vaciar')->name('carrito.vaciar')->block();
        Route::delete('carrito/{producto}', 'eliminar')->whereNumber('producto')->name('carrito.eliminar')->block();
        Route::post('confirmar', 'confirmar')->name('confirmar')->block();
        Route::get('{venta}/pdf', 'pdf')->whereNumber('venta')->name('pdf');
        Route::get('{venta}', 'show')->whereNumber('venta')->name('show');
    });

});
