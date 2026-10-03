@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Productos</h1><p class="text-muted mb-0">Gestión de productos e inventario.</p></div>
    <a href="{{ route('productos.create') }}" class="btn btn-primary">+ Nuevo producto</a>
</div>
<div class="card" data-live-region="products">
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
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark"><tr><th>ID</th><th>Producto</th><th>Stock</th><th>Stock mínimo</th><th>Precio</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr data-search-row data-search-value="{{ $producto->id_producto }} {{ $producto->nombre_producto }} {{ $producto->stock_actual }} {{ $producto->stock_minimo_alerta }} {{ $producto->precio_venta }} {{ $producto->stock_actual <= $producto->stock_minimo_alerta ? 'stock bajo' : 'disponible' }}" data-suggestion-label="{{ $producto->nombre_producto }} · #{{ $producto->id_producto }}">
                        <td>{{ $producto->id_producto }}</td><td>{{ $producto->nombre_producto }}</td>
                        <td>{{ $producto->stock_actual }}</td><td>{{ $producto->stock_minimo_alerta }}</td>
                        <td class="text-nowrap">S/ {{ number_format((float) $producto->precio_venta, 2) }}</td>
                        <td>
                            @if ($producto->stock_actual <= $producto->stock_minimo_alerta)
                                <span class="badge bg-danger">Stock bajo</span>
                            @else
                                <span class="badge bg-success">Disponible</span>
                            @endif
                        </td>
                        <td><div class="action-buttons">
                            <a href="{{ route('productos.edit', $producto) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form method="POST" action="{{ route('productos.destroy', $producto) }}" data-confirm="¿Deseas eliminar este producto?">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
</form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay productos registrados.</td></tr>
                @endforelse
                <tr data-search-empty hidden><td colspan="7" class="text-center text-muted py-4">No encontramos productos que coincidan.</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
