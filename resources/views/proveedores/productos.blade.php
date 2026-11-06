@extends('layouts.app')

@section('title', 'Productos del proveedor')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <a href="{{ route('proveedores.index') }}" class="btn btn-link btn-sm px-0 mb-2">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver a proveedores
        </a>
        <h1 class="h3 mb-1">{{ $proveedor->nombre_empresa }}</h1>
        <p class="text-muted mb-0">Productos y precios de compra de este proveedor.</p>
    </div>
    <button
        type="button"
        class="btn btn-primary btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#supplier-product-create-modal"
        @disabled($productosDisponibles->isEmpty())
    >
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Agregar producto
    </button>
</div>

<section class="card data-table-card supplier-products-panel" data-live-region="supplier-products">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Productos ofrecidos</h2>
        <span class="text-muted small">{{ $proveedor->productosProveedores->count() }} {{ Str::plural('producto', $proveedor->productosProveedores->count()) }}</span>
    </div>
    @include('proveedores._product-cards', ['proveedor' => $proveedor])
</section>

<div
    class="modal fade admin-form-modal"
    id="supplier-product-create-modal"
    tabindex="-1"
    aria-labelledby="supplier-product-create-title"
    aria-hidden="true"
    data-auto-open="{{ $errors->any() && old('_form') === 'proveedor-producto' ? 'true' : 'false' }}"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <p class="dashboard-panel-kicker mb-1">PRODUCTOS DEL PROVEEDOR</p>
                    <h2 class="modal-title h5 fw-bold" id="supplier-product-create-title">Agregar producto</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @if ($productosDisponibles->isNotEmpty())
                    <form action="{{ route('proveedores.productos.store', $proveedor) }}" method="POST">
                        @csrf
                        <input type="hidden" name="_form" value="proveedor-producto">
                        <div class="mb-3">
                            <label for="supplier-product" class="form-label">Producto</label>
                            <select
                                id="supplier-product"
                                name="id_producto"
                                class="form-select {{ $errors->has('id_producto') ? 'is-invalid' : '' }}"
                                required
                            >
                                <option value="">Seleccionar producto</option>
                                @foreach ($productosDisponibles as $producto)
                                    <option value="{{ $producto->id_producto }}" @selected(old('id_producto') == $producto->id_producto)>
                                        {{ $producto->nombre_producto }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_producto')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="supplier-product-price" class="form-label">Precio de compra (S/)</label>
                            <input
                                id="supplier-product-price"
                                type="number"
                                name="precio_compra"
                                value="{{ old('precio_compra') }}"
                                class="form-control {{ $errors->has('precio_compra') ? 'is-invalid' : '' }}"
                                min="0"
                                max="99999999.99"
                                step="0.01"
                                required
                            >
                            @error('precio_compra')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar producto</button>
                        </div>
                    </form>
                @else
                    <p class="text-muted mb-0">Todos los productos ya están asignados a este proveedor.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->any() && old('_form') === 'proveedor-producto')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.appFeedbackReady.then(function () {
                bootstrap.Modal.getOrCreateInstance(
                    document.getElementById('supplier-product-create-modal')
                ).show();
            });
        });
    </script>
@endif
@endpush
