@extends('layouts.guest')
@section('title', 'Acceso de administrador')
@section('content')
<p class="auth-description">Ingresa con el correo y la contraseña de tu cuenta administrativa.</p>
<form action="{{ route('login.store') }}" method="POST">
    @csrf
    <div class="auth-field">
        <label for="email" class="form-label">Correo electrónico</label>
        <div class="auth-input">
            <i class="bi bi-envelope" aria-hidden="true"></i>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" placeholder="nombre@empresa.com" maxlength="255" autocomplete="username" @error('email') aria-invalid="true" @enderror required autofocus>
        </div>
    </div>
    <div class="auth-field">
        <label for="password" class="form-label">Contraseña</label>
        <div class="auth-input">
            <i class="bi bi-lock" aria-hidden="true"></i>
            <input id="password" name="password" type="password" class="form-control has-toggle" placeholder="Ingresa tu contraseña" autocomplete="current-password" required>
            @include('auth._password-toggle', ['target' => 'password'])
        </div>
    </div>
    <div class="auth-options">
        <label class="auth-remember" for="remember">
            <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
            <span>Recordarme</span>
        </label>
        <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
    </div>
    <button class="btn auth-submit w-100" type="submit">Iniciar sesión <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
</form>
<div class="auth-links">
    <a class="auth-secondary-action" href="{{ route('admin.register') }}"><i class="bi bi-person-plus" aria-hidden="true"></i> Crear nueva cuenta de administrador</a>
    <a href="{{ route('login') }}">Volver a elegir tipo de cuenta</a>
</div>
@endsection