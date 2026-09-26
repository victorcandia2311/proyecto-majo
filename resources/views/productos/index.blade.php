@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Productos</h1><p class="text-muted mb-0">Gestión de productos e inventario.</p></div>
    <a href="{{ route('productos.create') }}" class="btn btn-primary">+ Nuevo producto</a>
</div>
<div class="card">
    <div class="card-header"><h2 class="h6 mb-0">Lista de productos</h2></div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark"><tr><th>ID</th><th>Producto</th><th>Stock</th><th>Stock mínimo</th><th>Precio</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr>
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
            </tbody>
        </table>
    </div>
</div>
@endsection
