@extends('layouts.guest')
@section('title', 'Recupera tu acceso')
@section('content')
<p class="auth-description">Te enviaremos un código de seis dígitos para restablecer tu contraseña. Será válido durante 15 minutos.</p>
<form action="{{ route('password.email') }}" method="POST">
    @csrf
    <div class="auth-field">
        <label for="email" class="form-label">Correo electrónico</label>
        <div class="auth-input">
            <i class="bi bi-envelope" aria-hidden="true"></i>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" placeholder="nombre@empresa.com" maxlength="255" autocomplete="username" required autofocus>
        </div>
    </div>
    <button class="btn auth-submit w-100" type="submit">Enviar código <i class="bi bi-send" aria-hidden="true"></i></button>
</form>
<div class="auth-links">
    <a href="{{ route('password.reset') }}">Ya tengo un código</a>
    <a href="{{ route('login') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al inicio de sesión</a>
</div>
@endsection
