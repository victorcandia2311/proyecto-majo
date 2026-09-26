@extends('layouts.app')

@section('title', 'Editar asistencia')

@section('content')
<h1 class="h3 mb-4">Editar asistencia</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('asistencia.update', $asistencia) }}" method="POST">
        @csrf
        @method('PUT')
        @include('asistencia._form', compact('asistencia', 'empleados'))
    </form>
</div></div>
@endsection
