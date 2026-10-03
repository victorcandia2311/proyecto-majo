@extends('layouts.guest')
@section('title', 'Acceso de vendedor')
@section('content')
<p class="auth-description">Escribe el usuario que te asignó el administrador.</p>
<form action="{{ route('login.seller.store') }}" method="POST">
    @csrf
    <div class="auth-field">
        <label for="usuario" class="form-label">Nombre de usuario</label>
        <div class="auth-input">
            <i class="bi bi-person" aria-hidden="true"></i>
            <input id="usuario" name="usuario" type="text" value="{{ old('usuario') }}" class="form-control" placeholder="usuario" maxlength="50" pattern="[A-Za-z0-9_\-]+" autocomplete="username" @error('usuario') aria-invalid="true" @enderror required autofocus>
        </div>
    </div>
    <button class="btn auth-submit w-100" type="submit">Entrar a ventas <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
</form>
<div class="auth-links"><a href="{{ route('login') }}">Volver a elegir tipo de cuenta</a></div>
@endsection