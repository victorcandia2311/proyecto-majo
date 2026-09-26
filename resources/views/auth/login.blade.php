@extends('layouts.guest')
@section('title', '¿Quién inicia sesión?')
@section('content')
<p class="auth-description">Elige el tipo de cuenta para continuar.</p>
<div class="auth-role-options">
    <a class="auth-role-option" href="{{ route('login.seller') }}">
        <span class="auth-role-icon"><i class="bi bi-bag-check" aria-hidden="true"></i></span>
        <span><strong>Vendedor</strong><small>Entrar al punto de venta</small></span>
        <i class="bi bi-arrow-right" aria-hidden="true"></i>
    </a>
    <a class="auth-role-option" href="{{ route('login.admin') }}">
        <span class="auth-role-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
        <span><strong>Administrador</strong><small>Acceso a toda la gestión</small></span>
        <i class="bi bi-arrow-right" aria-hidden="true"></i>
    </a>
</div>
@endsection
