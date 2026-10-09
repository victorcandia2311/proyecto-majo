@extends('layouts.app')

@section('title', 'Detalle de venta')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">Venta #{{ $venta->id_venta }}</h1>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('ventas.pdf', $venta) }}" class="btn btn-danger">Descargar PDF</a>
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Volver a ventas</a>
    </div>
</div>
<div class="card mb-4"><div class="card-body">
    <dl class="row mb-0">
        <div class="col-md-3"><dt>Fecha</dt><dd>{{ $venta->fecha_hora->format('d/m/Y H:i:s') }}</dd></div>
        <div class="col-md-3"><dt>Cliente</dt><dd>{{ $venta->cliente_nombre ?? 'Cliente general' }}</dd></div>
        <div class="col-md-3"><dt>Documento</dt><dd>{{ $venta->cliente_documento ?? '—' }}</dd></div>
        <div class="col-md-3"><dt>Método de pago</dt><dd>{{ $venta->metodo_pago }}</dd></div>
    </dl>
</div></div>
<div class="card">
    <div class="card-header"><h2 class="h6 mb-0">Productos vendidos</h2></div>
    <div class="table-responsive"><table class="table table-bordered align-middle">
        <thead class="table-dark"><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead>
        <tbody>
            @forelse ($detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto?->nombre_producto ?? 'Producto eliminado' }}</td><td>{{ $detalle->cantidad }}</td>
                    <td class="text-nowrap">S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                    <td class="text-nowrap">S/ {{ number_format($detalle->cantidad * (float) $detalle->precio_unitario, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Esta venta no tiene detalles registrados.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><th colspan="3" class="text-end">TOTAL</th><th class="text-nowrap">S/ {{ number_format((float) $venta->total_venta, 2) }}</th></tr></tfoot>
    </table></div>
</div>
@endsection
