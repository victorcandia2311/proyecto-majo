@extends('layouts.app')

@section('title', 'Inventario')

@section('content')

{{-- ═══ Page header ══════════════════════════════════════════════════════════ --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Gestión de inventario</h1>
        <p class="text-muted mb-0">Controla existencias y registra entradas.</p>
    </div>
    <span class="badge rounded-pill text-bg-light border px-3 py-2 fs-6">
        <i class="bi bi-boxes me-1 text-primary" aria-hidden="true"></i>
        {{ $productos->count() }} {{ Str::plural('producto', $productos->count()) }}
    </span>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     PANTALLA 1: MENÚ CENTRAL (visible por defecto)
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="menu-hub">

    <button type="button" class="hub-hero hub-hero-blue" data-show-view="tabla-movimientos" aria-label="Ver historial de inventario">
        <span class="hub-hero-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
        <span class="hub-hero-text">
            <strong>Ver historial de inventario</strong>
            <span>{{ $movimientos->total() }} {{ Str::plural('movimiento', $movimientos->total()) }} · Consulta entradas y salidas de productos</span>
        </span>
        <i class="bi bi-arrow-right-circle ms-auto fs-4 opacity-75" aria-hidden="true"></i>
    </button>

    <div class="hub-cards">
        <button
            type="button"
            class="hub-card"
            data-bs-toggle="offcanvas"
            data-bs-target="#inventory-restock-panel"
            aria-controls="inventory-restock-panel"
            @disabled($productos->isEmpty())
        >
            <span class="hub-card-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
            </span>
            <span class="hub-card-text">
                <strong>Registrar entrada</strong>
                <span>Aumentar stock existente</span>
            </span>
        </button>
    </div>

</div>{{-- /menu-hub --}}

{{-- ═══════════════════════════════════════════════════════════════════════════
     PANTALLA 2: TABLA — Historial de movimientos (oculta por defecto)
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="tabla-movimientos" class="d-none">

    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
        <button type="button" class="view-back-btn" data-show-view="menu-hub">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al menú
        </button>
        <h2 class="h5 mb-0 me-auto">Historial de inventario</h2>
    </div>

    <div class="card data-table-card" data-live-region="inventory-movements">
        <div class="card-header list-card-header">
            <h2 class="h6 mb-0">Movimientos registrados</h2>
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
                    <tr data-search-row data-search-value="{{ $movimiento->fecha_hora->format('d/m/Y H:i') }} {{ $movimiento->producto?->nombre_producto }} {{ $movimiento->tipo_movimiento }} {{ $movimiento->cantidad }} {{ $movimiento->motivo }}" data-suggestion-label="{{ $movimiento->producto?->nombre_producto ?? 'Producto eliminado' }} — {{ $movimiento->tipo_movimiento }} {{ $movimiento->cantidad }}">
                        <td class="text-nowrap data-table-secondary">{{ $movimiento->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td class="data-table-primary">{{ $movimiento->producto?->nombre_producto ?? 'Producto eliminado' }}</td>
                        <td>
                            @if ($movimiento->tipo_movimiento === 'ENTRADA')
                                <span class="data-badge data-badge-success"><i class="bi bi-arrow-down-left" aria-hidden="true"></i>Entrada</span>
                            @else
                                <span class="data-badge data-badge-danger"><i class="bi bi-arrow-up-right" aria-hidden="true"></i>Salida</span>
                            @endif
                        </td>
                        <td class="data-table-number">{{ $movimiento->cantidad }}</td>
                        <td class="data-table-secondary">{{ $movimiento->motivo ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="table-empty-cell">@include('components.empty-state', [
                        'title'       => 'Aún no hay movimientos',
                        'description' => 'Cuando recibas mercancía, registra una entrada.',
                        'actionUrl'   => '#inventory-restock-panel',
                        'actionLabel' => 'Registrar una entrada',
                        'actionTarget' => 'inventory-restock-panel',
                        'actionTargetType' => 'offcanvas',
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

</div>{{-- /tabla-movimientos --}}

{{-- ═══ Offcanvas: Registrar entrada ════════════════════════════════════════ --}}
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

@endsection

@push('scripts')
@if ($errors->any() && old('_form') === 'inventory-restock')
    <script>document.addEventListener('DOMContentLoaded', () => window.appFeedbackReady.then(() => bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('inventory-restock-panel')).show()));</script>
@endif

<script>
(function () {
    var VIEWS = ['menu-hub', 'tabla-movimientos'];
    var SESSION_KEY = 'inventario-view';

    function showView(targetId) {
        VIEWS.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { el.classList.toggle('d-none', id !== targetId); }
        });
        try { sessionStorage.setItem(SESSION_KEY, targetId); } catch (_) {}
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-show-view]');
        if (btn) { showView(btn.dataset.showView); }
    });

    try {
        if (sessionStorage.getItem(SESSION_KEY) === 'tabla-movimientos') {
            showView('tabla-movimientos');
        }
    } catch (_) {}
}());
</script>
@endpush
