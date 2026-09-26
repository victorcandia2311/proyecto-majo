<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
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
        return view('auth.admin-register', [
            'registrationEnabled' => config('auth.admin_registration_emails') !== [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        $request->merge(['email' => is_string($email) ? strtolower(trim($email)) : $email]);
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:72'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        unset($credentials['remember']);
        if (! Auth::attempt([...$credentials, 'rol' => 'admin'], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no son correctos.']);
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
            ->where('usuario', $data['usuario'])
            ->where('rol', 'empleado')
            ->first();

        if (! $empleado) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no está registrado como vendedor.']);
        }

        Auth::login($empleado);

        return $this->authenticatedRedirect($request);
    }

    public function registerAdmin(Request $request): RedirectResponse
    {
        $allowedEmails = config('auth.admin_registration_emails', []);
        if ($allowedEmails === []) {
            throw ValidationException::withMessages(['email' => 'El registro está cerrado. Configura los correos autorizados para administradores.']);
        }

        $email = $request->input('email');
        $request->merge(['email' => is_string($email) ? strtolower(trim($email)) : $email]);
        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'documento_identidad' => ['required', 'string', 'max:20', 'unique:empleado,documento_identidad'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::in($allowedEmails), 'unique:empleado,email'],
            'password' => ['required', 'string', 'confirmed', 'min:12', 'max:72'],
        ], [
            'email.in' => 'Este correo no está autorizado para crear una cuenta de administrador.',
        ]);

        $empleado = new Empleado;
        $empleado->forceFill([
            ...$data,
            'es_eventual' => false,
            'rol' => 'admin',
        ])->save();
        Auth::login($empleado);

        return $this->authenticatedRedirect($request);
    }

    private function authenticatedRedirect(Request $request): RedirectResponse
    {
        $request->session()->regenerate();
        $request->session()->forget(['carrito', 'url.intended', 'recovery_email']);

        return to_route($request->user()->homeRoute());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('status', 'Sesión cerrada correctamente.');
    }
}
