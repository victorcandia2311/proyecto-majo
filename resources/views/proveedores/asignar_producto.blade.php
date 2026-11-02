@extends('layouts.app')

@section('title', 'Asignar producto')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Asignar producto a proveedor</h1>
        <p class="text-muted mb-0">Registra el precio de compra de un producto con su proveedor.</p>
    </div>
    <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver a proveedores
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h2 class="h6 fw-bold mb-4">Registrar precio de compra</h2>
                @include('proveedores._precio-form', compact('productos', 'proveedores'))
            </div>
        </div>
    </div>
</div>
@endsection
