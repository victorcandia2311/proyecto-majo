@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Productos</h1><p class="text-muted mb-0">Gestión de productos e inventario.</p></div>
    <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#product-create-panel" aria-controls="product-create-panel">+ Nuevo producto</button>
</div>
<div class="card data-table-card" data-live-region="products">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Lista de productos</h2>
        <div class="list-search-wrap" data-list-search>
            <label class="visually-hidden" for="filter-products">Buscar productos</label>
            <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-products" type="search" placeholder="Buscar productos..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-products" data-list-search-input></div>
            <ul class="list-search-suggestions" id="suggestions-products" role="listbox" hidden></ul>
            <span class="list-search-count" data-search-count></span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover data-table align-middle">
            <thead class="data-table-head"><tr><th>ID</th><th>Producto</th><th>Stock</th><th>Stock mínimo</th><th>Precio</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr data-search-row data-search-value="{{ $producto->id_producto }} {{ $producto->nombre_producto }} {{ $producto->stock_actual }} {{ $producto->stock_minimo_alerta }} {{ $producto->precio_venta }} {{ $producto->stock_actual <= $producto->stock_minimo_alerta ? 'stock bajo' : 'disponible' }}" data-suggestion-label="{{ $producto->nombre_producto }} · #{{ $producto->id_producto }}">
                        <td class="data-table-secondary">#{{ $producto->id_producto }}</td><td class="data-table-primary">{{ $producto->nombre_producto }}</td>
                        <td class="data-table-number">{{ $producto->stock_actual }}</td><td class="data-table-secondary">{{ $producto->stock_minimo_alerta }}</td>
                        <td class="data-table-number">S/ {{ number_format((float) $producto->precio_venta, 2) }}</td>
                        <td>
                            @if ($producto->stock_actual <= $producto->stock_minimo_alerta)
                                <span class="data-badge data-badge-danger"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>Stock bajo</span>
                            @else
                                <span class="data-badge data-badge-success"><i class="bi bi-check-circle" aria-hidden="true"></i>Disponible</span>
                            @endif
                        </td>
                        <td><div class="action-buttons">
                            <a href="{{ route('productos.edit', $producto) }}" class="btn-icon btn-icon-primary" aria-label="Editar {{ $producto->nombre_producto }}" title="Editar"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                            <form method="POST" action="{{ route('productos.destroy', $producto) }}" data-confirm="¿Deseas eliminar este producto?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon btn-icon-danger" aria-label="Eliminar {{ $producto->nombre_producto }}" title="Eliminar"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                            </form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="table-empty-cell">@include('components.empty-state', [
                        'title' => 'Tu catálogo está vacío',
                        'description' => 'Registra el primer producto para empezar a controlar existencias y ventas.',
                        'actionUrl' => '#product-create-panel',
                        'actionLabel' => 'Registrar producto',
                        'actionTarget' => 'product-create-panel',
                        'actionTargetType' => 'offcanvas',
                    ])</td></tr>
                @endforelse
                <tr data-search-empty hidden><td colspan="7" class="text-center text-muted py-4">No encontramos productos que coincidan.</td></tr>
            </tbody>
        </table>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="product-create-panel" aria-labelledby="product-create-title">
    <div class="offcanvas-header border-bottom">
        <div><p class="dashboard-panel-kicker mb-1">CATÁLOGO</p><h2 class="offcanvas-title h5 fw-bold" id="product-create-title">Nuevo producto</h2></div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_form" value="product-create">
            @include('productos._form', ['producto' => null])
        </form>
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->any() && old('_form') === 'product-create')
    <script>document.addEventListener('DOMContentLoaded', () => window.appFeedbackReady.then(() => bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('product-create-panel')).show()));</script>
@endif
@endpush
