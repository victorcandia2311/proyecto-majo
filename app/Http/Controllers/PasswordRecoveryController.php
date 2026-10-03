<?php

namespace App\Http\Controllers;

use App\Mail\CodigoRecuperacion;
use App\Models\Empleado;
use Closure;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PasswordRecoveryController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $usuario = $this->validatedUsername($request);
        $empleado = Empleado::query()->whereRaw('LOWER(usuario) = ?', [$usuario])
            ->where('rol', 'admin')->whereNotNull('password')->first();
        if ($empleado) {
            $codigo = (string) random_int(100000, 999999);
            $hash = Hash::make($codigo);
            $empleado->forceFill(['codigo_verificacion' => $hash, 'codigo_expira_en' => now()->addMinutes(15)])->save();
            try {
                Mail::to(config('auth.admin_registration_code_email'))->send(new CodigoRecuperacion($codigo));
            } catch (Throwable $exception) {
                Empleado::whereKey($empleado->getKey())->where('codigo_verificacion', $hash)
                    ->update(['codigo_verificacion' => null, 'codigo_expira_en' => null]);
                report($exception);
            }
        }
        $request->session()->put('recovery_username', $usuario);

        return to_route('password.reset')->with('status', 'Si el usuario tiene una cuenta de administrador habilitada, recibirás un código en el buzón autorizado.');
    }

    public function edit(Request $request): View
    {
        $usuario = $request->session()->get('recovery_username', '');

        return view('auth.reset-password', compact('usuario'));
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario = $this->validatedUsername($request);
        $data = $request->validate([
            'codigo_verificacion' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'password' => ['required', 'string', 'confirmed', 'max:72', Password::min(12)->mixedCase()->numbers(),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('La contraseña supera el máximo de 72 bytes permitido.');
                    }
                },
            ],
        ], [
            'codigo_verificacion.regex' => 'Ingresa el código de seis dígitos.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'Usa al menos 12 caracteres.',
        ]);
        $empleado = DB::transaction(function () use ($usuario, $data): Empleado {
            $empleado = Empleado::query()->whereRaw('LOWER(usuario) = ?', [$usuario])
                ->where('rol', 'admin')->lockForUpdate()->first();
            if (! $empleado?->codigo_verificacion || ! $empleado->codigo_expira_en?->isFuture()
                || ! Hash::check($data['codigo_verificacion'], $empleado->codigo_verificacion)) {
                throw ValidationException::withMessages(['codigo_verificacion' => 'El código es incorrecto o ha expirado. Solicita uno nuevo.']);
            }
            $empleado->forceFill([
                'password' => Hash::make($data['password']),
                'codigo_verificacion' => null,
                'codigo_expira_en' => null,
                'remember_token' => Str::random(60),
            ])->save();

            return $empleado;
        });
        event(new PasswordReset($empleado));
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('status', 'Contraseña actualizada. Inicia sesión con tu nueva contraseña.');
    }

    private function validatedUsername(Request $request): string
    {
        $usuario = $request->input('usuario');
        $request->merge(['usuario' => is_string($usuario) ? strtolower(trim($usuario)) : $usuario]);

        return $request->validate(['usuario' => ['required', 'string', 'alpha_dash:ascii', 'max:50']])['usuario'];
    }
}
