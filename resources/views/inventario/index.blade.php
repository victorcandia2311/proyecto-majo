@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 mb-1">Gestión de inventario</h1><p class="text-muted mb-0">Controla existencias y registra entradas.</p></div>
    <button type="button" class="btn btn-primary rounded-3 px-3 py-2" data-bs-toggle="offcanvas" data-bs-target="#inventory-restock-panel" aria-controls="inventory-restock-panel" @disabled($productos->isEmpty())>+ Registrar entrada</button>
</div>
<div class="card mb-4">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><h2 class="h6 mb-1">¿Llegó nueva mercancía?</h2><p class="text-muted mb-0">Registra una entrada para actualizar el stock y guardar el movimiento.</p></div>
        <button type="button" class="btn btn-outline-primary rounded-3 px-3 py-2" data-bs-toggle="offcanvas" data-bs-target="#inventory-restock-panel" aria-controls="inventory-restock-panel" @disabled($productos->isEmpty())>Aumentar inventario</button>
    </div>
</div>
<div class="offcanvas offcanvas-end admin-form-panel" tabindex="-1" id="inventory-restock-panel" aria-labelledby="inventory-restock-title">
    <div class="offcanvas-header border-bottom">
        <div><p class="dashboard-panel-kicker mb-1">EXISTENCIAS</p><h2 class="offcanvas-title h5 fw-bold" id="inventory-restock-title">Registrar entrada</h2></div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <form id="inventory-restock-form" action="{{ route('inventario.aumentar') }}" method="POST">
            @csrf
            <input type="hidden" name="_form" value="inventory-restock">
            <div class="mb-3">
                <label for="inventory-product" class="form-label">Producto</label>
                <select name="id_producto" id="inventory-product" class="form-select" data-native-select required>
                    <option value="">Seleccionar producto</option>
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id_producto }}" @selected(old('id_producto') == $producto->id_producto)>{{ $producto->nombre_producto }} — Stock: {{ $producto->stock_actual }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="inventory-quantity" class="form-label">Cantidad a ingresar</label>
                <input type="number" id="inventory-quantity" name="cantidad" value="{{ old('cantidad', 1) }}" min="1" max="2147483647" step="1" class="form-control" required>
            </div>
            <div class="mb-4">
                <label for="inventory-reason" class="form-label">Motivo <span class="text-muted">(opcional)</span></label>
                <input type="text" id="inventory-reason" name="motivo" value="{{ old('motivo') }}" maxlength="200" placeholder="Reabastecimiento" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 w-100" @disabled($productos->isEmpty())>Confirmar entrada</button>
        </form>
        @if ($productos->isEmpty())<p class="text-muted mt-3 mb-0">Primero <a href="{{ route('productos.create') }}">registra un producto</a>.</p>@endif
    </div>
</div>
<div class="card data-table-card mb-4" data-live-region="inventory-products">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Inventario actual</h2>
        <div class="list-search-wrap" data-list-search>
            <label class="visually-hidden" for="filter-inventory">Buscar inventario</label>
            <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-inventory" type="search" placeholder="Buscar productos..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-inventory" data-list-search-input></div>
            <ul class="list-search-suggestions" id="suggestions-inventory" role="listbox" hidden></ul>
            <span class="list-search-count" data-search-count></span>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-hover data-table align-middle">
        <thead class="data-table-head"><tr><th>ID</th><th>Producto</th><th>Stock actual</th><th>Stock mínimo</th><th>Precio</th><th>Estado</th></tr></thead>
        <tbody>
            @forelse ($productos as $producto)
                <tr data-search-row data-search-value="{{ $producto->id_producto }} {{ $producto->nombre_producto }} {{ $producto->stock_actual }} {{ $producto->stock_minimo_alerta }} {{ $producto->precio_venta }} {{ $producto->stock_actual <= $producto->stock_minimo_alerta ? 'stock bajo' : 'disponible' }}" data-suggestion-label="{{ $producto->nombre_producto }} · Stock {{ $producto->stock_actual }}">
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
                </tr>
            @empty
                <tr><td colspan="6" class="table-empty-cell">@include('components.empty-state', [
                    'title' => 'No hay productos en inventario',
                    'description' => 'Registra un producto antes de gestionar sus existencias.',
                    'actionUrl' => route('productos.create'),
                    'actionLabel' => 'Registrar producto',
                ])</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="6" class="text-center text-muted py-4">No encontramos productos que coincidan.</td></tr>
        </tbody>
    </table></div>
</div>
<div class="card data-table-card" data-live-region="inventory-movements">
    <div class="card-header list-card-header">
        <h2 class="h6 mb-0">Historial de movimientos</h2>
        <div class="list-search-wrap" data-list-search>
            <label class="visually-hidden" for="filter-movements">Buscar movimientos</label>
            <div class="list-search-field"><i class="bi bi-search" aria-hidden="true"></i><input id="filter-movements" type="search" placeholder="Buscar movimientos..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions-movements" data-list-search-input></div>
            <ul class="list-search-suggestions" id="suggestions-movements" role="listbox" hidden></ul>
            <span class="list-search-count" data-search-count></span>
        </div>
    </div>
    <div class="table-responsive"><table class="table table-hover data-table align-middle">
        <thead class="data-table-head"><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Motivo</th></tr></thead>
        <tbody>
            @forelse ($movimientos as $movimiento)
                <tr data-search-row data-search-value="{{ $movimiento->fecha_hora->format('d/m/Y H:i') }} {{ $movimiento->producto?->nombre_producto }} {{ $movimiento->tipo_movimiento }} {{ $movimiento->cantidad }} {{ $movimiento->motivo }}" data-suggestion-label="{{ $movimiento->producto?->nombre_producto ?? 'Producto eliminado' }} · {{ $movimiento->tipo_movimiento }} {{ $movimiento->cantidad }}">
                    <td class="text-nowrap data-table-secondary">{{ $movimiento->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td class="data-table-primary">{{ $movimiento->producto?->nombre_producto ?? 'Producto eliminado' }}</td>
                    <td>
                        @if ($movimiento->tipo_movimiento === 'ENTRADA')
                            <span class="data-badge data-badge-success"><i class="bi bi-arrow-down-left" aria-hidden="true"></i>Entrada</span>
                        @else
                            <span class="data-badge data-badge-danger"><i class="bi bi-arrow-up-right" aria-hidden="true"></i>Salida</span>
                        @endif
                    </td>
                    <td class="data-table-number">{{ $movimiento->cantidad }}</td><td class="data-table-secondary">{{ $movimiento->motivo ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="table-empty-cell">@include('components.empty-state', [
                    'title' => 'Aún no hay movimientos',
                    'description' => 'Cuando recibas mercancía, registra una entrada en el formulario de arriba.',
                    'actionUrl' => '#inventory-restock-form',
                    'actionLabel' => 'Registrar una entrada',
                ])</td></tr>
            @endforelse
            <tr data-search-empty hidden><td colspan="5" class="text-center text-muted py-4">No encontramos movimientos que coincidan.</td></tr>
        </tbody>
    </table></div>
    @if ($movimientos->hasPages())
        <div class="list-pagination" data-async-pagination>
            <span>Mostrando {{ $movimientos->firstItem() }}–{{ $movimientos->lastItem() }} de {{ $movimientos->total() }} movimientos</span>
            {{ $movimientos->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if ($errors->any() && old('_form') === 'inventory-restock')
    <script>document.addEventListener('DOMContentLoaded', () => window.appFeedbackReady.then(() => bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('inventory-restock-panel')).show()));</script>
@endif
@endpush
