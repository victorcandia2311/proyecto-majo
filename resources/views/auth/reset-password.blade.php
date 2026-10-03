@extends('layouts.guest')
@section('title', 'Crea una nueva contraseña')
@section('content')
<p class="auth-description">Ingresa el código que recibiste por correo y elige tu nueva contraseña.</p>
<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <div class="auth-field">
        <label for="usuario" class="form-label">Usuario de administrador</label>
        <div class="auth-input">
            <i class="bi bi-person" aria-hidden="true"></i>
            <input id="usuario" name="usuario" type="text" value="{{ old('usuario', $usuario) }}" class="form-control" placeholder="usuario_01" maxlength="50" pattern="[A-Za-z0-9_\-]+" autocomplete="username" required>
        </div>
    </div>
    <div class="auth-field">
        <label for="codigo_verificacion" class="form-label">Código de verificación</label>
        <div class="auth-input">
            <i class="bi bi-shield-check" aria-hidden="true"></i>
            <input id="codigo_verificacion" name="codigo_verificacion" type="text" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" class="form-control" placeholder="000000" autocomplete="one-time-code" required autofocus>
        </div>
    </div>
    <div class="auth-field">
        <label for="password" class="form-label">Nueva contraseña</label>
        <div class="auth-input">
            <i class="bi bi-lock" aria-hidden="true"></i>
            <input id="password" name="password" type="password" class="form-control has-toggle" placeholder="Tu nueva contraseña" minlength="12" maxlength="72" autocomplete="new-password" aria-describedby="password-help" required>
            @include('auth._password-toggle', ['target' => 'password'])
        </div>
        <div id="password-help" class="form-text">Al menos 12 caracteres, con mayúsculas, minúsculas y números.</div>
    </div>
    <div class="auth-field">
        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
        <div class="auth-input">
            <i class="bi bi-lock" aria-hidden="true"></i>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control has-toggle" placeholder="Repite tu nueva contraseña" minlength="12" maxlength="72" autocomplete="new-password" required>
            @include('auth._password-toggle', ['target' => 'password_confirmation'])
        </div>
    </div>
    <button class="btn auth-submit w-100" type="submit">Verificar y guardar <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
</form>
<div class="auth-links">
    <a href="{{ route('password.request') }}">Solicitar un nuevo código</a>
    <a href="{{ route('login') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al inicio de sesión</a>
</div>
@endsection
