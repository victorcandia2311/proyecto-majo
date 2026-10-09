<?php

use App\Mail\CodigoRecuperacion;
use App\Mail\CodigoRegistroAdministrador;
use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Venta;
use Database\Seeders\AuthUsersSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.block_store' => 'array']);
    DB::purge('sqlite');

    Schema::create('empleado', function (Blueprint $table) {
        $table->increments('id_empleado');
        $table->string('nombres', 100);
        $table->string('documento_identidad', 20)->nullable()->unique();
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

    Schema::create('producto', function (Blueprint $table) {
        $table->increments('id_producto');
        $table->string('nombre_producto', 150);
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
});

test('authentication pages render with protected forms', function (string $routeName) {
    $response = $this->get(route($routeName))->assertOk()->assertSee('MAJO')->assertDontSee('PES');
    if ($routeName === 'login') {
        $response->assertSee('¿Quién inicia sesión?')->assertSee('Vendedor')->assertSee('Administrador');
    } else {
        $response->assertSee('name="_token"', false);
    }
})->with(['login', 'login.admin', 'login.seller', 'admin.register', 'password.request', 'password.reset']);

test('admin and seller login forms ask for username without a seller PIN', function () {
    $this->get(route('login.admin'))->assertOk()->assertSee('Usuario')->assertSee('Contraseña')
        ->assertSee('name="usuario"', false)->assertDontSee('Correo electrónico')->assertDontSee('nombre@empresa.com');
    $this->get(route('login.seller'))->assertOk()->assertSee('name="usuario"', false)->assertDontSee('name="pin"', false);
});

test('every business route requires login including writes and PDFs', function () {
    foreach (Route::getRoutes() as $route) {
        if (! preg_match('/^(dashboard|productos|proveedores|empleados|inventario|ventas|asistencia)\\./', $route->getName() ?? '')) {
            continue;
        }
        $uri = preg_replace('/\\{[^}]+\\}/', '99999', $route->uri());
        $this->call($route->methods()[0], $uri)->assertRedirectToRoute('login');
    }
});

test('employees receive 403 for every administrative route', function () {
    $this->actingAs(Empleado::factory()->withAccess()->create());
    foreach (Route::getRoutes() as $route) {
        if (! preg_match('/^(dashboard|productos|proveedores|empleados|inventario|asistencia)\\./', $route->getName() ?? '')) {
            continue;
        }
        $uri = preg_replace('/\\{[^}]+\\}/', '99999', $route->uri());
        $this->call($route->methods()[0], $uri)->assertForbidden();
    }
});

test('administrator credentials regenerate the session and redirect to the dashboard', function () {
    $empleado = Empleado::factory()->withAccess('admin')->create(['email' => 'admin@majo.com', 'usuario' => 'admin_user']);
    $this->withSession(['carrito' => ['stale'], 'url.intended' => route('dashboard.index')]);
    $sessionId = session()->getId();

    $this->post(route('login.store'), ['usuario' => 'ADMIN_USER', 'password' => 'AccesoSeguro123!', 'rol' => 'empleado'])
        ->assertRedirectToRoute('dashboard.index')->assertSessionMissing('carrito');
    $this->assertAuthenticatedAs($empleado);
    expect(session()->getId())->not->toBe($sessionId);
    $this->get(route('login'))->assertRedirectToRoute('dashboard.index');
    $this->get('/')->assertRedirectToRoute('dashboard.index');
});

test('seller signs in with username only and cannot access administration or attendance', function () {
    $empleado = Empleado::factory()->withAccess()->create(['usuario' => 'vendedor_1', 'password' => null]);
    $this->withSession(['carrito' => ['stale']]);
    $sessionId = session()->getId();

    $this->post(route('login.seller.store'), ['usuario' => ' VENDEDOR_1 '])
        ->assertRedirectToRoute('ventas.index')->assertSessionMissing('carrito');
    $this->assertAuthenticatedAs($empleado);
    expect(session()->getId())->not->toBe($sessionId);
    $this->get(route('asistencia.index'))->assertForbidden();
    $this->get(route('productos.index'))->assertForbidden();
});

test('seller access rejects unknown usernames and administrator accounts', function () {
    $admin = Empleado::factory()->withAccess('admin')->create(['usuario' => 'admin_user']);
    $this->post(route('login.seller.store'), ['usuario' => 'missing'])->assertSessionHasErrors('usuario');
    $this->post(route('login.seller.store'), ['usuario' => $admin->usuario])->assertSessionHasErrors('usuario');
    $this->assertGuest();
});

test('administrator login rejects seller username even when seller has a password', function () {
    Empleado::factory()->withAccess()->create(['usuario' => 'vendedor', 'password' => 'AccesoSeguro123!']);
    $this->post(route('login.store'), ['usuario' => 'vendedor', 'password' => 'AccesoSeguro123!'])
        ->assertSessionHasErrors('usuario');
    $this->assertGuest();
});

test('administrator can request and verify a code before creating an account', function () {
    Mail::fake();
    config(['auth.admin_registration_code_email' => 'victorcandia2311@gmail.com']);
    $this->postJson(route('admin.register.code'))->assertOk()->assertJsonStructure(['status']);
    $codigo = null;
    Mail::assertSent(CodigoRegistroAdministrador::class, function (CodigoRegistroAdministrador $mail) use (&$codigo): bool {
        $codigo = $mail->codigo;

        return $mail->hasTo('victorcandia2311@gmail.com') && preg_match('/^[0-9]{6}$/', $mail->codigo) === 1;
    });

    $this->postJson(route('admin.register.code.verify'), ['codigo_verificacion' => '000000'])
        ->assertUnprocessable()->assertJsonValidationErrors('codigo_verificacion');
    $this->assertDatabaseCount('empleado', 0);
    $this->postJson(route('admin.register.code.verify'), ['codigo_verificacion' => $codigo])
        ->assertOk()->assertJsonStructure(['status']);
    $this->get(route('admin.register'))->assertOk()
        ->assertSee('data-registration-details', false)
        ->assertDontSee('name="password_confirmation"', false)
        ->assertDontSee('name="email"', false)
        ->assertDontSee('name="documento_identidad"', false);

    $this->post(route('admin.register.store'), [
        'nombres' => 'Nombre', 'apellidos' => 'Apellido',
        'usuario' => 'ADMIN_1', 'password' => 'ContrasenaSegura123!',
    ])
        ->assertRedirectToRoute('dashboard.index');
    $admin = Empleado::sole();
    expect($admin->rol)->toBe('admin')->and($admin->nombres)->toBe('Nombre Apellido')
        ->and($admin->documento_identidad)->toBeNull()->and($admin->email)->toBeNull()->and($admin->usuario)->toBe('admin_1');
    $this->assertAuthenticatedAs($admin);
});

test('administrator code request needs no account fields before verification', function () {
    Mail::fake();
    $this->postJson(route('admin.register.code'))
        ->assertOk()->assertJsonStructure(['status']);
    Mail::assertSent(CodigoRegistroAdministrador::class);
});

test('administrator registration cannot create an account until a code is verified', function () {
    Mail::fake();
    $this->post(route('admin.register.store'), [
        'nombres' => 'Nombre', 'apellidos' => 'Apellido', 'usuario' => 'admin_1',
        'password' => 'ContrasenaSegura123!',
    ])->assertSessionHasErrors('codigo_verificacion');
    $this->assertDatabaseCount('empleado', 0);
});

test('administrator registration code guesses are limited by session', function () {
    Mail::fake();
    $this->post(route('admin.register.code'))->assertRedirectToRoute('admin.register');
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->post(route('admin.register.code.verify'), ['codigo_verificacion' => '000000'])
            ->assertSessionHasErrors('codigo_verificacion');
    }
    $this->post(route('admin.register.code.verify'), ['codigo_verificacion' => '000000'])->assertStatus(429);
    $this->assertDatabaseCount('empleado', 0);
});

test('administrator registration codes expire after fifteen minutes', function () {
    Mail::fake();
    $this->freezeTime();
    $this->post(route('admin.register.code'))->assertRedirectToRoute('admin.register');
    $codigo = null;
    Mail::assertSent(CodigoRegistroAdministrador::class, function (CodigoRegistroAdministrador $mail) use (&$codigo): bool {
        $codigo = $mail->codigo;

        return true;
    });
    $this->travel(16)->minutes();
    $this->post(route('admin.register.code.verify'), ['codigo_verificacion' => $codigo])
        ->assertSessionHasErrors('codigo_verificacion');
    $this->assertDatabaseCount('empleado', 0);
});

test('invalid credentials and legacy employees without passwords cannot log in', function (bool $legacy) {
    if ($legacy) {
        Empleado::factory()->create(['email' => 'persona@majo.com']);
    } else {
        Empleado::factory()->withAccess()->create(['email' => 'persona@majo.com', 'usuario' => 'persona']);
    }
    $this->post(route('login.store'), ['usuario' => 'persona', 'password' => 'incorrecta'])
        ->assertSessionHasErrors(['usuario' => 'El usuario o la contraseña no son correctos.']);
    $this->assertGuest();
})->with([false, true]);

test('login attempts are throttled per account', function () {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('login.store'), ['usuario' => 'desconocido', 'password' => 'wrong'])
            ->assertSessionHasErrors('usuario');
    }
    $this->post(route('login.store'), ['usuario' => 'DESCONOCIDO', 'password' => 'wrong'])->assertStatus(429);
    $this->assertGuest();
});

test('seller login attempts are throttled per username across different IPs', function () {
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.'.$attempt])
            ->post(route('login.seller.store'), ['usuario' => 'vendedor_limited'])
            ->assertSessionHasErrors('usuario');
    }
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.99'])
        ->post(route('login.seller.store'), ['usuario' => 'vendedor_limited'])
        ->assertStatus(429);
    $this->assertGuest();
});

test('logout invalidates the session cart and csrf token', function () {
    $this->actingAs(Empleado::factory()->withAccess()->create())->withSession(['carrito' => ['product']]);
    $token = csrf_token();
    $this->post(route('logout'))->assertRedirectToRoute('login')->assertSessionMissing('carrito');
    $this->assertGuest();
    expect(csrf_token())->not->toBe($token);
    $this->get(route('ventas.index'))->assertRedirectToRoute('login');
});

test('employees can sell view PDFs and record attendance without admin navigation', function () {
    $empleado = Empleado::factory()->withAccess()->create();
    $this->actingAs($empleado);
    $producto = Producto::factory()->create(['stock_actual' => 5, 'precio_venta' => '10.00']);
    $this->get(route('ventas.index'))->assertOk()->assertSee('Cerrar sesión')
        ->assertDontSee('PES')
        ->assertDontSee('href="'.route('productos.index').'"', false)
        ->assertDontSee('href="'.route('inventario.index').'"', false)
        ->assertDontSee('href="'.route('asistencia.index').'"', false);
    $this->get(route('asistencia.index'))->assertForbidden();
    $this->post(route('asistencia.ingreso'), ['id_empleado' => $empleado->getKey()])->assertForbidden();
    $this->post(route('ventas.carrito.agregar'), ['id_producto' => $producto->getKey(), 'cantidad' => 2])->assertRedirect();
    $this->post(route('ventas.confirmar'), ['metodo_pago' => 'Efectivo'])->assertRedirectToRoute('ventas.index');
    expect($producto->fresh()->stock_actual)->toBe(3);
    $venta = Venta::sole();
    $this->get(route('ventas.show', $venta))->assertOk();
    $this->get(route('ventas.pdf', $venta))->assertOk()->assertDownload();
});

test('recovery emails contain a hashed single use six digit code with fifteen minute expiry', function () {
    $this->travelTo(now()->startOfSecond());
    Mail::fake();
    $empleado = Empleado::factory()->withAccess('admin')->create(['usuario' => 'admin_mail']);
    $this->post(route('password.email'), ['usuario' => $empleado->usuario])
        ->assertRedirectToRoute('password.reset')->assertSessionHas('recovery_username', $empleado->usuario);
    Mail::assertSent(CodigoRecuperacion::class, function (CodigoRecuperacion $mail) use ($empleado): bool {
        return $mail->hasTo(config('auth.admin_registration_code_email'))
            && preg_match('/^[0-9]{6}$/', $mail->codigo)
            && Hash::check($mail->codigo, $empleado->fresh()->codigo_verificacion);
    });
    expect($empleado->fresh()->codigo_expira_en->equalTo(now()->addMinutes(15)))->toBeTrue();
    expect($empleado->fresh()->toArray())->not->toHaveKeys(['password', 'codigo_verificacion', 'codigo_expira_en', 'remember_token']);
});

test('recovery does not reveal whether an email has an enabled account', function () {
    Mail::fake();
    $empleado = Empleado::factory()->withAccess('admin')->create(['usuario' => 'admin_recovery']);
    $vendedor = Empleado::factory()->withAccess()->create(['email' => 'vendedor@majo.com', 'usuario' => 'vendedor_recovery']);
    $known = $this->post(route('password.email'), ['usuario' => $empleado->usuario])->assertRedirectToRoute('password.reset');
    $message = session('status');
    $this->post(route('password.email'), ['usuario' => 'missing_user'])
        ->assertRedirectToRoute('password.reset')->assertSessionHas('status', $message);
    $this->post(route('password.email'), ['usuario' => $vendedor->usuario])
        ->assertRedirectToRoute('password.reset')->assertSessionHas('status', $message);
    expect($vendedor->fresh()->codigo_verificacion)->toBeNull();
    Mail::assertSentCount(1);
});

test('resending is throttled and a later resend replaces the previous code', function () {
    Mail::fake();
    $this->freezeTime();
    $empleado = Empleado::factory()->withAccess('admin')->create(['usuario' => 'admin_resend']);
    $this->post(route('password.email'), ['usuario' => $empleado->usuario])->assertRedirect();
    $previousHash = $empleado->fresh()->codigo_verificacion;
    $this->post(route('password.email'), ['usuario' => $empleado->usuario])->assertStatus(429);
    $this->travel(61)->seconds();
    $this->post(route('password.email'), ['usuario' => $empleado->usuario])->assertRedirect();
    expect($empleado->fresh()->codigo_verificacion)->not->toBe($previousHash);
    Mail::assertSentCount(2);
});

test('valid recovery code changes the bcrypt password and cannot be reused', function () {
    $empleado = Empleado::factory()->withAccess('admin')->create([
        'usuario' => 'admin_recovery',
        'codigo_verificacion' => Hash::make('123456'), 'codigo_expira_en' => now()->addMinutes(15),
    ]);
    $data = ['usuario' => $empleado->usuario, 'codigo_verificacion' => '123456', 'password' => 'NuevaClaveSegura123!', 'password_confirmation' => 'NuevaClaveSegura123!'];
    $this->post(route('password.update'), $data)->assertRedirectToRoute('login');
    $empleado->refresh();
    expect(Hash::check($data['password'], $empleado->password))->toBeTrue();
    expect(password_get_info($empleado->password)['algoName'])->toBe('bcrypt');
    expect($empleado->codigo_verificacion)->toBeNull();
    expect($empleado->codigo_expira_en)->toBeNull();
    $this->assertGuest();
    $this->post(route('password.update'), $data)->assertSessionHasErrors('codigo_verificacion');
    $this->post(route('login.store'), ['usuario' => $empleado->usuario, 'password' => $data['password']])->assertRedirectToRoute('dashboard.index');
});

test('incorrect expired and other account codes leave passwords unchanged', function (string $failure) {
    $this->freezeTime();
    $empleado = Empleado::factory()->withAccess('admin')->create([
        'usuario' => 'admin_wrong_code',
        'codigo_verificacion' => Hash::make('123456'),
        'codigo_expira_en' => $failure === 'expired' ? now() : now()->addMinutes(15),
    ]);
    $this->post(route('password.update'), [
        'usuario' => $failure === 'other account' ? 'other_admin' : $empleado->usuario,
        'codigo_verificacion' => $failure === 'incorrect' ? '000000' : '123456',
        'password' => 'NuevaClaveSegura123!', 'password_confirmation' => 'NuevaClaveSegura123!',
    ])->assertSessionHasErrors('codigo_verificacion')->assertSessionMissing('_old_input.codigo_verificacion')
        ->assertSessionMissing('_old_input.password');
    expect($empleado->fresh()->password)->toBe($empleado->password);
})->with(['incorrect', 'expired', 'other account']);

test('code guessing is limited across sessions and IP addresses for the same account', function () {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.'.($attempt + 1)])
            ->post(route('password.update'), [
                'usuario' => 'persona', 'codigo_verificacion' => '000000',
                'password' => 'NuevaClaveSegura123!', 'password_confirmation' => 'NuevaClaveSegura123!',
            ])->assertSessionHasErrors('codigo_verificacion');
    }
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.100'])->post(route('password.update'), ['usuario' => 'PERSONA'])->assertStatus(429);
});

test('recovery rejects weak mismatched and oversized multibyte passwords', function (string $password, string $confirmation) {
    $this->post(route('password.update'), [
        'usuario' => 'persona', 'codigo_verificacion' => '123456',
        'password' => $password, 'password_confirmation' => $confirmation,
    ])->assertSessionHasErrors('password');
})->with([
    ['short', 'short'], ['onlylowercaseletters', 'onlylowercaseletters'],
    ['NuevaClaveSegura123!', 'different'],
    [str_repeat('é', 40).'Aa1', str_repeat('é', 40).'Aa1'],
]);

test('sessions with an old password are rejected after a password change', function () {
    $empleado = Empleado::factory()->withAccess('admin')->create();
    $this->actingAs($empleado)->get(route('ventas.index'))->assertOk();
    $empleado->forceFill(['password' => 'NuevaClaveSegura123!'])->save();
    $this->get(route('ventas.index'))->assertRedirectToRoute('login');
    $this->assertGuest();
});

test('profile updates cannot overwrite authentication fields', function () {
    $admin = Empleado::factory()->withAccess('admin')->create();
    $empleado = Empleado::factory()->withAccess()->create(['usuario' => 'vendedor_profile']);
    $this->actingAs($admin)->put(route('empleados.update', $empleado), [
        'nombres' => 'Nombre editado', 'documento_identidad' => $empleado->documento_identidad, 'es_eventual' => false,
        'rol' => 'admin', 'email' => 'attacker@majo.com', 'password' => 'ChangedPassword123!',
    ])->assertRedirectToRoute('empleados.index');
    expect($empleado->fresh()->rol)->toBe('empleado');
    expect($empleado->fresh()->email)->toBe($empleado->email);
    expect($empleado->fresh()->password)->toBe($empleado->password);
});

test('base user seeding is repeatable without resetting credentials or roles', function () {
    $this->seed(AuthUsersSeeder::class);
    $admin = Empleado::where('email', 'admin@majo.com')->sole();
    $vendedor = Empleado::where('email', 'vendedor@majo.com')->sole();
    expect($admin->rol)->toBe('admin');
    expect($admin->usuario)->toBe('admin');
    expect($vendedor->rol)->toBe('empleado');
    expect($vendedor->usuario)->toBe('vendedor')->and($vendedor->password)->toBeNull();
    expect(password_get_info($admin->password)['algoName'])->toBe('bcrypt');
    $this->seed(AuthUsersSeeder::class);
    $this->assertDatabaseCount('empleado', 2);
    expect($admin->fresh()->password)->toBe($admin->password);
});

test('seeding assigns a username to an existing default administrator without changing its password', function () {
    $admin = Empleado::factory()->withAccess('admin')->create([
        'email' => 'admin@majo.com', 'usuario' => null,
    ]);
    $password = $admin->password;

    $this->seed(AuthUsersSeeder::class);

    expect($admin->fresh()->usuario)->toBe('admin');
    expect($admin->fresh()->password)->toBe($password);
});

test('authentication forms reject requests without csrf tokens outside tests', function (string $routeName) {
    $this->app['env'] = 'production';
    $this->post(route($routeName))->assertStatus(419);
})->with(['login.store', 'login.seller.store', 'admin.register.code', 'admin.register.code.verify', 'admin.register.store', 'logout', 'password.email', 'password.update']);

test('verification email renders the code and expiry in html and plain text', function () {
    $mail = new CodigoRecuperacion('123456');
    $mail->assertSeeInHtml('123456');
    $mail->assertSeeInText('123456');
    $mail->assertSeeInHtml('15 minutos');

    $registrationMail = new CodigoRegistroAdministrador('654321');
    $registrationMail->assertSeeInHtml('654321');
    $registrationMail->assertSeeInText('654321');
    $registrationMail->assertSeeInText('15 minutos');
});

test('mail delivery failures invalidate the undelivered code without exposing account existence', function () {
    $empleado = Empleado::factory()->withAccess('admin')->create(['usuario' => 'admin_mail_failure']);
    Exceptions::fake();
    Mail::shouldReceive('to')->once()->with(config('auth.admin_registration_code_email'))->andReturnSelf();
    Mail::shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP unavailable'));
    $this->post(route('password.email'), ['usuario' => $empleado->usuario])->assertRedirectToRoute('password.reset')
        ->assertSessionHas('status', 'Si el usuario tiene una cuenta de administrador habilitada, recibirás un código en el buzón autorizado.');
    expect($empleado->fresh()->codigo_verificacion)->toBeNull();
    expect($empleado->fresh()->codigo_expira_en)->toBeNull();
    Exceptions::assertReported(RuntimeException::class);
});

test('malformed username input receives validation errors instead of server errors', function (string $routeName) {
    $this->post(route($routeName), ['usuario' => ['invalid'], 'password' => 'Invalid123456!'])
        ->assertSessionHasErrors('usuario');
})->with(['password.email', 'password.update']);

test('remember me creates a persistent login only when selected', function (bool $remember) {
    $empleado = Empleado::factory()->withAccess('admin')->create(['usuario' => 'admin_remember']);
    $response = $this->post(route('login.store'), [
        'usuario' => $empleado->usuario, 'password' => 'AccesoSeguro123!',
        ...($remember ? ['remember' => '1'] : []),
    ])->assertRedirectToRoute('dashboard.index');
    $cookie = Auth::getRecallerName();

    if ($remember) {
        $response->assertCookie($cookie);
        expect($empleado->fresh()->remember_token)->not->toBeNull();
    } else {
        $response->assertCookieMissing($cookie);
        expect($empleado->fresh()->remember_token)->toBeNull();
    }
})->with([true, false]);

test('logout revokes a remembered login', function () {
    $empleado = Empleado::factory()->withAccess('admin')->create(['usuario' => 'admin_logout']);
    $this->post(route('login.store'), ['usuario' => $empleado->usuario, 'password' => 'AccesoSeguro123!', 'remember' => '1'])
        ->assertRedirectToRoute('dashboard.index');
    $token = $empleado->fresh()->remember_token;
    $this->post(route('logout'))->assertRedirectToRoute('login');
    expect($empleado->fresh()->remember_token)->not->toBe($token);
    $this->assertGuest();
});
