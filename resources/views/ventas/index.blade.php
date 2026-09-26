@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">Gestión de ventas</h1>
    <a href="{{ route('ventas.nueva') }}" class="btn btn-primary">Nueva venta</a>
</div>
@include('ventas._carrito', compact('productos', 'carrito', 'totalCarrito'))
<div class="card">
    <div class="card-header"><h2 class="h6 mb-0">Historial de ventas</h2></div>
    <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
        <thead class="table-dark"><tr><th>N.º</th><th>Fecha</th><th>Cliente</th><th>Método de pago</th><th>Total</th><th>Acción</th></tr></thead>
        <tbody>
            @forelse ($ventas as $venta)
                <tr>
                    <td>#{{ $venta->id_venta }}</td><td class="text-nowrap">{{ $venta->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->cliente_nombre ?? 'Cliente general' }}</td><td>{{ $venta->metodo_pago }}</td>
                    <td class="text-nowrap">S/ {{ number_format((float) $venta->total_venta, 2) }}</td>
                    <td><a href="{{ route('ventas.show', $venta) }}" class="btn btn-info btn-sm">Ver detalle</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No hay ventas registradas.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>
@endsection
