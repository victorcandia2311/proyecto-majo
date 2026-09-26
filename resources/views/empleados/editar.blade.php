@extends('layouts.app')

@section('title', 'Editar empleado')

@section('content')
<h1 class="h3 mb-4">Editar empleado</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('empleados.update', $empleado) }}" method="POST">
        @csrf
        @method('PUT')
        @include('empleados._form', ['empleado' => $empleado])
    </form>
</div></div>
@endsection
