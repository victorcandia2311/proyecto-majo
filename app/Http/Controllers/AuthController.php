<?php

namespace App\Http\Controllers;

use App\Mail\CodigoRegistroAdministrador;
use App\Models\Empleado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    private const REGISTRATION_CODE_INVALID = 'El código es incorrecto o ha expirado. Solicita uno nuevo.';

    public function home(Request $request): RedirectResponse
    {
        return to_route($request->user()?->homeRoute() ?? 'login');
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function adminLogin(): View
    {
        return view('auth.admin-login');
    }

    public function sellerLogin(): View
    {
        return view('auth.seller-login');
    }

    public function createAdminRegistration(): View
    {
        $request = request();

        return view('auth.admin-register', [
            'registrationCodeSent' => $this->registrationCodeIsValid($request) || $this->registrationCodeIsVerified($request),
            'registrationCodeVerified' => $this->registrationCodeIsVerified($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $usuario = $request->input('usuario');
        $request->merge(['usuario' => is_string($usuario) ? strtolower(trim($usuario)) : $usuario]);
        $credentials = $request->validate([
            'usuario' => ['required', 'string', 'alpha_dash:ascii', 'max:50'],
            'password' => ['required', 'string', 'max:72'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        unset($credentials['remember']);
        if (! Auth::attempt([...$credentials, 'rol' => 'admin'], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['usuario' => 'El usuario o la contraseña no son correctos.']);
        }

        return $this->authenticatedRedirect($request);
    }

    public function storeSeller(Request $request): RedirectResponse
    {
        $usuario = $request->input('usuario');
        $request->merge(['usuario' => is_string($usuario) ? strtolower(trim($usuario)) : $usuario]);
        $data = $request->validate([
            'usuario' => ['required', 'string', 'alpha_dash:ascii', 'max:50'],
        ]);
        $empleado = Empleado::query()
            ->whereRaw('LOWER(usuario) = ?', [$data['usuario']])
            ->where('rol', 'empleado')
            ->first();

        if (! $empleado) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no está registrado como vendedor.']);
        }
        Auth::login($empleado);

        return $this->authenticatedRedirect($request);
    }

    public function sendAdminRegistrationCode(Request $request): JsonResponse|RedirectResponse
    {
        $codigo = (string) random_int(100000, 999999);

        try {
            Mail::to(config('auth.admin_registration_code_email'))->send(new CodigoRegistroAdministrador($codigo));
        } catch (Throwable $exception) {
            $request->session()->forget($this->registrationCodeSessionKeys());
            report($exception);
            throw ValidationException::withMessages(['codigo_verificacion' => 'No se pudo enviar el código. Revisa la configuración del correo e inténtalo de nuevo.']);
        }

        $request->session()->put([
            'admin_registration_code_hash' => Hash::make($codigo),
            'admin_registration_code_expires_at' => now()->addMinutes(15)->timestamp,
            'admin_registration_code_verified' => false,
        ]);

        $status = 'Enviamos un código a '.config('auth.admin_registration_code_email').'. Es válido durante 15 minutos.';

        if ($request->expectsJson()) {
            return response()->json(['status' => $status]);
        }

        return to_route('admin.register')->withInput($request->except(['password', 'password_confirmation']))->with('status', $status);
    }

    public function verifyAdminRegistrationCode(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'codigo_verificacion' => ['required', 'digits:6'],
        ], [
            'codigo_verificacion.digits' => 'Ingresa el código de seis dígitos.',
        ]);

        if (! $this->registrationCodeIsValid($request)
            || ! Hash::check($data['codigo_verificacion'], $request->session()->get('admin_registration_code_hash', ''))) {
            throw ValidationException::withMessages(['codigo_verificacion' => self::REGISTRATION_CODE_INVALID]);
        }

        $request->session()->put('admin_registration_code_verified', true);
        $request->session()->forget('admin_registration_code_hash');
        $status = 'Código verificado. Completa tus datos para crear la cuenta.';

        if ($request->expectsJson()) {
            return response()->json(['status' => $status]);
        }

        return to_route('admin.register')->with('status', $status);
    }

    public function registerAdmin(Request $request): RedirectResponse
    {
        $usuario = $request->input('usuario');
        $request->merge(['usuario' => is_string($usuario) ? strtolower(trim($usuario)) : $usuario]);

        if (! $this->registrationCodeIsVerified($request)) {
            throw ValidationException::withMessages(['codigo_verificacion' => self::REGISTRATION_CODE_INVALID]);
        }

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:50'],
            'apellidos' => ['required', 'string', 'max:49'],
            'usuario' => ['required', 'string', 'alpha_dash:ascii', 'max:50', Rule::unique(Empleado::class, 'usuario')],
            'password' => ['required', 'string', 'min:12', 'max:72'],
        ]);
        $data['nombres'] = trim($data['nombres'].' '.$data['apellidos']);
        unset($data['apellidos']);

        $empleado = new Empleado;
        $empleado->forceFill([
            ...$data,
            'documento_identidad' => null,
            'es_eventual' => false,
            'rol' => 'admin',
        ])->save();
        $request->session()->forget($this->registrationCodeSessionKeys());
        Auth::login($empleado);

        return $this->authenticatedRedirect($request);
    }

    private function authenticatedRedirect(Request $request): RedirectResponse
    {
        $request->session()->regenerate();
        $request->session()->forget(['carrito', 'url.intended', 'recovery_email', 'recovery_username', ...$this->registrationCodeSessionKeys()]);

        return to_route($request->user()->homeRoute());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('status', 'Sesión cerrada correctamente.');
    }

    private function registrationCodeIsValid(Request $request): bool
    {
        return is_string($request->session()->get('admin_registration_code_hash'))
            && (int) $request->session()->get('admin_registration_code_expires_at', 0) > now()->timestamp;
    }

    private function registrationCodeIsVerified(Request $request): bool
    {
        return $request->session()->get('admin_registration_code_verified') === true
            && (int) $request->session()->get('admin_registration_code_expires_at', 0) > now()->timestamp;
    }

    /** @return list<string> */
    private function registrationCodeSessionKeys(): array
    {
        return [
            'admin_registration_code_hash',
            'admin_registration_code_expires_at',
            'admin_registration_code_verified',
        ];
    }
}
