@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
<h1 class="h3 mb-4">Gestión de inventario</h1>
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Aumentar inventario</h2></div>
    <div class="card-body">
        <form action="{{ route('inventario.aumentar') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label for="id_producto" class="form-label">Producto</label>
                <select name="id_producto" id="id_producto" class="form-select" required>
                    <option value="">Seleccionar producto</option>
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id_producto }}" @selected(old('id_producto') == $producto->id_producto)>{{ $producto->nombre_producto }} — Stock: {{ $producto->stock_actual }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="cantidad" class="form-label">Cantidad a ingresar</label>
                <input type="number" id="cantidad" name="cantidad" value="{{ old('cantidad', 1) }}" min="1" max="2147483647" step="1" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label for="motivo" class="form-label">Motivo</label>
                <input type="text" id="motivo" name="motivo" value="{{ old('motivo') }}" maxlength="200" placeholder="Reabastecimiento" class="form-control">
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-success w-100" @disabled($productos->isEmpty())>+ Aumentar</button></div>
        </form>
        @if ($productos->isEmpty())<p class="text-muted mt-3 mb-0">Primero <a href="{{ route('productos.create') }}">registra un producto</a>.</p>@endif
    </div>
</div>
<div class="card mb-4">
    <div class="card-header"><h2 class="h6 mb-0">Inventario actual</h2></div>
    <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
        <thead class="table-dark"><tr><th>ID</th><th>Producto</th><th>Stock actual</th><th>Stock mínimo</th><th>Precio</th><th>Estado</th></tr></thead>
        <tbody>
            @forelse ($productos as $producto)
                <tr>
                    <td>{{ $producto->id_producto }}</td><td>{{ $producto->nombre_producto }}</td>
                    <td>{{ $producto->stock_actual }}</td><td>{{ $producto->stock_minimo_alerta }}</td>
                    <td class="text-nowrap">S/ {{ number_format((float) $producto->precio_venta, 2) }}</td>
                    <td><span class="badge {{ $producto->stock_actual <= $producto->stock_minimo_alerta ? 'bg-danger' : 'bg-success' }}">{{ $producto->stock_actual <= $producto->stock_minimo_alerta ? 'Stock bajo' : 'Disponible' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No hay productos registrados.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>
<div class="card">
    <div class="card-header"><h2 class="h6 mb-0">Historial de movimientos</h2></div>
    <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
        <thead class="table-dark"><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Motivo</th></tr></thead>
        <tbody>
            @forelse ($movimientos as $movimiento)
                <tr>
                    <td class="text-nowrap">{{ $movimiento->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td>{{ $movimiento->producto->nombre_producto }}</td>
                    <td><span class="badge {{ $movimiento->tipo_movimiento === 'ENTRADA' ? 'bg-success' : 'bg-danger' }}">{{ $movimiento->tipo_movimiento }}</span></td>
                    <td>{{ $movimiento->cantidad }}</td><td>{{ $movimiento->motivo ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No existen movimientos registrados.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>
@endsection
