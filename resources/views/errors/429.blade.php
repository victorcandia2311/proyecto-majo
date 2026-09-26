@extends('layouts.guest')
@section('title', 'Demasiados intentos')
@section('content')
<p class="text-muted">Por seguridad, espera unos minutos antes de intentarlo otra vez. Para verificar códigos, el límite es de cinco intentos cada 15 minutos.</p>
<a class="btn btn-primary w-100" href="{{ route('login') }}">Volver al inicio</a>
@endsection
