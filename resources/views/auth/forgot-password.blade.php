@extends('layouts.guest')
@section('title', 'Recupera tu acceso')
@section('content')
<p class="auth-description">Te enviaremos un código de seis dígitos para restablecer tu contraseña. Será válido durante 15 minutos.</p>
<form action="{{ route('password.email') }}" method="POST">
    @csrf
    <div class="auth-field">
          <label for="usuario" class="form-label">Usuario de administrador</label>
        <div class="auth-input">
                <i class="bi bi-person" aria-hidden="true"></i>
                <input id="usuario" name="usuario" type="text" value="{{ old('usuario') }}" class="form-control" placeholder="usuario_01" maxlength="50" pattern="[A-Za-z0-9_\-]+" autocomplete="username" required autofocus>
        </div>
    </div>
    <button class="btn auth-submit w-100" type="submit">Enviar código <i class="bi bi-send" aria-hidden="true"></i></button>
</form>
<div class="auth-links">
    <a href="{{ route('password.reset') }}">Ya tengo un código</a>
    <a href="{{ route('login') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al inicio de sesión</a>
</div>
@endsection
