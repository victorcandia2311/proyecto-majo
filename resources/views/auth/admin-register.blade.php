@extends('layouts.guest')
@section('title', 'Crear cuenta de administrador')
@section('content')
@if (! $registrationEnabled)
    <div class="auth-notice auth-error" role="status"><i class="bi bi-lock" aria-hidden="true"></i><span>El registro está cerrado. Un responsable debe agregar tu correo a ADMIN_REGISTRATION_EMAILS en la configuración del entorno.</span></div>
@else
    <p class="auth-description">Completa tus datos. Solo se aceptan correos autorizados para administrar el sistema.</p>
    <form action="{{ route('admin.register.store') }}" method="POST">
        @csrf
        <div class="auth-field">
            <label for="nombres" class="form-label">Nombre completo</label>
            <div class="auth-input"><i class="bi bi-person" aria-hidden="true"></i><input id="nombres" name="nombres" type="text" value="{{ old('nombres') }}" class="form-control" maxlength="100" autocomplete="name" required></div>
        </div>
        <div class="auth-field">
            <label for="documento_identidad" class="form-label">Documento de identidad</label>
            <div class="auth-input"><i class="bi bi-card-text" aria-hidden="true"></i><input id="documento_identidad" name="documento_identidad" type="text" value="{{ old('documento_identidad') }}" class="form-control" maxlength="20" required></div>
        </div>
        <div class="auth-field">
            <label for="email" class="form-label">Correo autorizado</label>
            <div class="auth-input"><i class="bi bi-envelope" aria-hidden="true"></i><input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" maxlength="255" autocomplete="email" required></div>
        </div>
        <div class="auth-field">
            <label for="password" class="form-label">Contraseña</label>
            <div class="auth-input"><i class="bi bi-lock" aria-hidden="true"></i><input id="password" name="password" type="password" class="form-control" minlength="12" maxlength="72" autocomplete="new-password" required></div>
        </div>
        <div class="auth-field">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <div class="auth-input"><i class="bi bi-lock-fill" aria-hidden="true"></i><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" minlength="12" maxlength="72" autocomplete="new-password" required></div>
        </div>
        <button class="btn auth-submit w-100" type="submit">Crear cuenta <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
    </form>
@endif
<div class="auth-links"><a href="{{ route('login.admin') }}">Volver al acceso de administrador</a></div>
@endsection