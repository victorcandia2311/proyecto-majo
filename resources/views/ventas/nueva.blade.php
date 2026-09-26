@extends('layouts.app')

@section('title', 'Nueva venta')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">Nueva venta</h1>
    <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Volver a ventas</a>
</div>
@include('ventas._carrito', compact('productos', 'carrito', 'totalCarrito'))
@endsection
