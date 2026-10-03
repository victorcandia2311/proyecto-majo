@extends('layouts.guest')
@section('title', 'Crear cuenta de administrador')
@section('content')
    <p class="auth-description">Solicita el código de verificación para continuar.</p>
    <form action="{{ route('admin.register.store') }}" method="POST" data-admin-registration data-code-url="{{ route('admin.register.code') }}" data-verify-url="{{ route('admin.register.code.verify') }}">
        @csrf
        <div data-registration-request @if($registrationCodeSent) hidden @endif>
            <button class="btn auth-submit w-100" type="submit" formaction="{{ route('admin.register.code') }}" formmethod="POST" formnovalidate data-request-registration-code>Solicitar código <i class="bi bi-send" aria-hidden="true"></i></button>
        </div>
        <div data-registration-code-step @if(!$registrationCodeSent || $registrationCodeVerified) hidden @endif>
            <div class="auth-field">
                <label for="codigo_verificacion" class="form-label">Código de verificación</label>
                <div class="auth-input"><i class="bi bi-shield-check" aria-hidden="true"></i><input id="codigo_verificacion" name="codigo_verificacion" type="text" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" class="form-control" data-digits-only @if($registrationCodeSent && !$registrationCodeVerified) required @else disabled @endif></div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn auth-submit flex-fill" type="submit" formaction="{{ route('admin.register.code.verify') }}" formmethod="POST" formnovalidate data-verify-registration-code>Verificar código <i class="bi bi-check2" aria-hidden="true"></i></button>
                <button class="btn btn-outline-secondary flex-fill" type="submit" formaction="{{ route('admin.register.code') }}" formmethod="POST" formnovalidate data-request-registration-code>Reenviar</button>
            </div>
        </div>
        <div data-registration-details @if(!$registrationCodeVerified) hidden @endif>
            <div class="auth-field">
                <label for="nombres" class="form-label">Nombres</label>
                <div class="auth-input"><i class="bi bi-person" aria-hidden="true"></i><input id="nombres" name="nombres" type="text" value="{{ old('nombres') }}" class="form-control" maxlength="50" autocomplete="given-name" @if($registrationCodeVerified) required @else disabled @endif></div>
            </div>
            <div class="auth-field">
                <label for="apellidos" class="form-label">Apellidos</label>
                <div class="auth-input"><i class="bi bi-person" aria-hidden="true"></i><input id="apellidos" name="apellidos" type="text" value="{{ old('apellidos') }}" class="form-control" maxlength="49" autocomplete="family-name" @if($registrationCodeVerified) required @else disabled @endif></div>
            </div>
            <div class="auth-field">
                <label for="usuario" class="form-label">Usuario</label>
                <div class="auth-input"><i class="bi bi-person" aria-hidden="true"></i><input id="usuario" name="usuario" type="text" value="{{ old('usuario') }}" class="form-control" maxlength="50" pattern="[A-Za-z0-9_\-]+" autocomplete="username" @if($registrationCodeVerified) required @else disabled @endif></div>
            </div>
            <div class="auth-field">
                <label for="password" class="form-label">Contraseña</label>
                <div class="auth-input"><i class="bi bi-lock" aria-hidden="true"></i><input id="password" name="password" type="password" class="form-control" minlength="12" maxlength="72" autocomplete="new-password" @if($registrationCodeVerified) required @else disabled @endif></div>
            </div>
            <button class="btn auth-submit w-100" type="submit">Crear cuenta <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </div>
        <div class="auth-notice" data-registration-feedback role="status" aria-live="polite" hidden></div>
    </form>
<div class="auth-links"><a href="{{ route('login.admin') }}">Volver al acceso de administrador</a></div>
@endsection