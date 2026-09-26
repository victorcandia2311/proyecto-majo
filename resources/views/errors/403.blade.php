@extends('layouts.guest')
@section('title', 'Acceso no autorizado')
@section('content')
<p class="text-muted">Tu cuenta no tiene permiso para acceder a este módulo.</p>
<a class="btn btn-primary w-100" href="{{ route(auth()->user()?->homeRoute() ?? 'login') }}">Volver al sistema</a>
@endsection
