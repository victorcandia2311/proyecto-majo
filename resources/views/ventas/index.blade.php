@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">Gestión de ventas</h1>
    <a href="{{ route('ventas.nueva') }}" class="btn btn-primary">Nueva venta</a>
</div>
@include('ventas._carrito', compact('productos', 'carrito', 'totalCarrito'))
<div class="card" data-live-region="sales">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Historial de ventas</h2>
        <div class="d-flex flex-wrap align-items-end gap-2">
            <div><label class="form-label small mb-1" for="sales-date-from">Desde</label><input id="sales-date-from" class="form-control form-control-sm" type="date" data-date-filter-start></div>
            <div><label class="form-label small mb-1" for="sales-date-to">Hasta</label><input id="sales-date-to" class="form-control form-control-sm" type="date" data-date-filter-end></div>
            <div class="list-search-wrap" data-list-search>
                <label class="visually-hidden" for="filter-sales">Buscar ventas</label>
                <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-sales" type="search" placeholder="Buscar ventas..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-sales" data-list-search-input></div>
                <ul class="list-search-suggestions" id="suggestions-sales" role="listbox" hidden></ul>
                <span class="list-search-count" data-search-count></span>
            </div>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-bordered table-hover align-middle">
        <thead class="table-dark"><tr><th>N.º</th><th>Fecha</th><th>Cliente</th><th>Método de pago</th><th>Total</th><th>Acción</th></tr></thead>
        <tbody>
            @forelse ($ventas as $venta)
                <tr data-search-row data-search-date="{{ $venta->fecha_hora->format('Y-m-d') }}" data-search-value="{{ $venta->id_venta }} {{ $venta->fecha_hora->format('d/m/Y H:i') }} {{ $venta->cliente_nombre ?? 'cliente general' }} {{ $venta->cliente_documento }} {{ $venta->metodo_pago }} {{ $venta->total_venta }}" data-suggestion-label="#{{ $venta->id_venta }} · {{ $venta->cliente_nombre ?? 'Cliente general' }} · {{ $venta->metodo_pago }}">
                    <td>#{{ $venta->id_venta }}</td><td class="text-nowrap">{{ $venta->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td>{{ $venta->cliente_nombre ?? 'Cliente general' }}</td><td>{{ $venta->metodo_pago }}</td>
                    <td class="text-nowrap">S/ {{ number_format((float) $venta->total_venta, 2) }}</td>
                    <td><a href="{{ route('ventas.show', $venta) }}" class="btn btn-info btn-sm">Ver detalle</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No hay ventas registradas.</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="6" class="text-center text-muted py-4">No encontramos ventas que coincidan.</td></tr>
        </tbody>
    </table></div>
</div>
@endsection
