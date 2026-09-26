@extends('layouts.app')

@section('title', 'Asignar producto')

@section('content')
<div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
    <h1 class="h3 mb-0">Asignar producto a proveedor</h1>
    <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">Volver a proveedores</a>
</div>
<div class="card">
    <div class="card-header"><h2 class="h6 mb-0">Registrar precio de compra</h2></div>
    <div class="card-body">@include('proveedores._precio-form', compact('productos', 'proveedores'))</div>
</div>
@endsection
