@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0">Gestión de ventas</h1>
    <a href="{{ route('ventas.nueva') }}" class="btn btn-primary">Nueva venta</a>
</div>
@include('ventas._carrito', compact('productos', 'carrito', 'totalCarrito'))
<div class="card data-table-card" data-live-region="sales">
    <div class="card-header list-card-header sales-history-header">
        <h2 class="h6 mb-0">Historial de ventas</h2>
        <div class="sales-history-tools">
            <form action="{{ route('ventas.index') }}" method="GET" class="sales-date-filters" data-date-filter-form>
                <div><label class="form-label small mb-1" for="sales-date-from">Desde</label><input id="sales-date-from" name="fecha_desde" value="{{ $fechaDesde }}" class="form-control form-control-sm" type="date" data-date-filter-start></div>
                <div><label class="form-label small mb-1" for="sales-date-to">Hasta</label><input id="sales-date-to" name="fecha_hasta" value="{{ $fechaHasta }}" class="form-control form-control-sm" type="date" data-date-filter-end></div>
                <button type="submit" class="btn btn-outline-primary btn-sm">Filtrar</button>
            </form>
            <div class="list-search-wrap" data-list-search>
                <label class="visually-hidden" for="filter-sales">Buscar ventas</label>
                <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-sales" type="search" placeholder="Buscar ventas..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-sales" data-list-search-input></div>
                <ul class="list-search-suggestions" id="suggestions-sales" role="listbox" hidden></ul>
                <span class="list-search-count" data-search-count></span>
            </div>
        </div>
    </div>
    @if ($filtrandoPorFecha)
        <div class="sales-filter-total" role="status">
            <i class="bi bi-calendar-check" aria-hidden="true"></i>
            <span>Total de ventas del periodo seleccionado</span>
            <strong>S/ {{ number_format((float) $totalVentasFiltradas, 2) }}</strong>
        </div>
    @endif
    <div class="table-responsive"><table class="table table-hover data-table align-middle">
        <thead class="data-table-head"><tr><th>N.º</th><th>Fecha</th><th>Cliente</th><th>Método de pago</th><th>Total</th><th class="text-end">Acción</th></tr></thead>
        <tbody>
            @forelse ($ventas as $venta)
                <tr data-search-row data-search-date="{{ $venta->fecha_hora->format('Y-m-d') }}" data-search-value="{{ $venta->id_venta }} {{ $venta->fecha_hora->format('d/m/Y H:i') }} {{ $venta->cliente_nombre ?? 'cliente general' }} {{ $venta->cliente_documento }} {{ $venta->metodo_pago }} {{ $venta->total_venta }}" data-suggestion-label="#{{ $venta->id_venta }} · {{ $venta->cliente_nombre ?? 'Cliente general' }} · {{ $venta->metodo_pago }}">
                    <td class="data-table-secondary">#{{ $venta->id_venta }}</td><td class="text-nowrap data-table-secondary">{{ $venta->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td class="data-table-primary">{{ $venta->cliente_nombre ?? 'Cliente general' }}</td>
                    <td><span class="data-badge data-badge-neutral">{{ $venta->metodo_pago }}</span></td>
                    <td class="data-table-number">S/ {{ number_format((float) $venta->total_venta, 2) }}</td>
                    <td><div class="action-buttons"><a href="{{ route('ventas.show', $venta) }}" class="btn-icon btn-icon-primary" aria-label="Ver detalle de venta {{ $venta->id_venta }}" title="Ver detalle"><i class="bi bi-eye" aria-hidden="true"></i></a></div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="table-empty-cell">@include('components.empty-state', [
                    'title' => 'Todavía no hay ventas',
                    'description' => 'Inicia una venta para que aparezca en este historial.',
                    'actionUrl' => route('ventas.nueva'),
                    'actionLabel' => 'Registrar una venta',
                ])</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="6" class="text-center text-muted py-4">No encontramos ventas que coincidan.</td></tr>
        </tbody>
    </table></div>
    @if ($ventas->hasPages())
        <div class="list-pagination" data-async-pagination>
            <span>Mostrando {{ $ventas->firstItem() }}–{{ $ventas->lastItem() }} de {{ $ventas->total() }} ventas</span>
            {{ $ventas->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
